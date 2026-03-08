#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script per generare automaticamente tutti gli INSERT SQL
per i giorni MERCOLEDÌ, GIOVEDÌ, VENERDÌ, SABATO
leggendo i dati dal file Excel
"""

import openpyxl
import sys
import os
import re

# Percorso del file Excel
excel_file = os.path.join(os.path.dirname(__file__), '..', 'template', 'Orario Soci MusicAll.xlsx')

# Mapping aule (colonne Excel)
AULE_MAP = {
    'C': ('MIDI', 'Chitarra'),
    'D': ('PIANO', 'Piano e Canto'),
    'E': ('MAGNA', 'Canto'),
    'F': ('JAZZ', 'Batteria'),
    'G': ('POP', 'Canto'),
    'H': ('ROCK', 'Canto')
}

# Docenti per aula (dalle righe 6-7 dell'Excel)
DOCENTI_DEFAULT = {
    'MIDI': ['MARCANTE', 'TESSITORE'],
    'PIANO': ['SALVUCCI', 'PACCHIAROTTI'],
    'MAGNA': ['DI CRESCE'],
    'JAZZ': ['ALBERINI'],
    'POP': ['BUONO', 'LORITO'],
    'ROCK': ['MARCANTE', 'DI GIORGIO']
}

def extract_time_and_name(cell_value):
    """Estrae orario e cognome del socio da una cella"""
    if not cell_value:
        return None, None
    
    value = str(cell_value).strip()
    
    # Salta valori non utili
    skip_words = ['DOCENTE', 'AULA', 'NOTE', 'PAUSA', 'DA FARE', 'NO ', 'PROVA']
    if any(word in value.upper() for word in skip_words):
        return None, None
    
    # Cerca pattern orario (es: 10:00/11:00 o 10:00-11:00 o 10.00-11.00)
    time_pattern = r'(\d{1,2}[:\.]\d{2})\s*[/-]\s*(\d{1,2}[:\.]\d{2})'
    time_match = re.search(time_pattern, value)
    
    if not time_match:
        return None, None
    
    # Estrai orario
    start_time = time_match.group(1).replace('.', ':')
    end_time = time_match.group(2).replace('.', ':')
    
    # Formato HH:MM
    if len(start_time.split(':')[0]) == 1:
        start_time = '0' + start_time
    if len(end_time.split(':')[0]) == 1:
        end_time = '0' + end_time
    
    # Estrai nome allievo (rimuovi orario e pulisci)
    name_part = re.sub(time_pattern, '', value).strip()
    name_part = re.sub(r'\([^)]*\)', '', name_part).strip()  # Rimuovi note tra parentesi
    
    # Prendi prima parola in maiuscolo (cognome)
    words = name_part.split()
    cognome = None
    for word in words:
        if word.isupper() and len(word) > 2:
            cognome = word
            break
    
    if not cognome:
        return None, None
    
    return (start_time, end_time), cognome


def get_docente_for_slot(aula, ora_inizio, col_letter, ws):
    """Determina il docente in base all'aula e all'orario"""
    docenti = DOCENTI_DEFAULT.get(aula, ['UNKNOWN'])
    
    # Leggi docente dal foglio Excel (righe 6-7)
    docente_cell_6 = ws.cell(row=6, column=ord(col_letter) - ord('A') + 1).value
    docente_cell_7 = ws.cell(row=7, column=ord(col_letter) - ord('A') + 1).value
    
    # Estrai cognome dal docente
    if docente_cell_6:
        doc_text = str(docente_cell_6).upper()
        docente_name = doc_text.split()[0] if doc_text.split() else None
        if docente_name:
            docenti[0] = docente_name
    
    if docente_cell_7 and len(docenti) > 1:
        doc_text = str(docente_cell_7).upper()
        docente_name = doc_text.split()[0] if doc_text.split() else None
        if docente_name:
            docenti.append(docente_name)
    
    # Per PIANO: dopo le 16:00 usa secondo docente
    if aula == 'PIANO' and len(docenti) > 1:
        ora_num = int(ora_inizio.split(':')[0])
        if ora_num >= 16:
            return docenti[1] if len(docenti) > 1 else docenti[0]
    
    return docenti[0]


def process_day(giorno, giorno_it):
    """Processa un giorno e genera gli INSERT SQL"""
    print(f"\n{'='*60}")
    print(f"PROCESSAMENTO {giorno_it.upper()}")
    print(f"{'='*60}\n")
    
    try:
        wb = openpyxl.load_workbook(excel_file, data_only=True)
        
        if giorno not in wb.sheetnames:
            print(f"ERRORE: Foglio {giorno} non trovato!")
            return None
        
        ws = wb[giorno]
        
        sql_statements = []
        sql_statements.append(f"-- =============================================")
        sql_statements.append(f"-- LEZIONI {giorno_it.upper()}")
        sql_statements.append(f"-- Generato automaticamente dall'Excel")
        sql_statements.append(f"-- =============================================\n")
        
        lezioni = []
        
        # Scansiona tutte le colonne (C-H) e righe (8-45)
        for col_idx in range(3, 9):  # C=3, D=4, E=5, F=6, G=7, H=8
            col_letter = chr(ord('A') + col_idx - 1)
            
            if col_letter not in AULE_MAP:
                continue
            
            aula_nome, materia = AULE_MAP[col_letter]
            print(f"Scansiono {aula_nome} (colonna {col_letter})...")
            
            current_time = None
            
            for row in range(10, 50):
                cell = ws.cell(row=row, column=col_idx)
                
                if not cell.value:
                    continue
                
                cell_str = str(cell.value).strip()
                
                # Skip valori inutili
                skip_words = ['DOCENTE', 'AULA', 'NOTE', 'PAUSA', 'DA FARE', 'NO ']
                if any(word in cell_str.upper() for word in skip_words):
                    continue
                
                # Prova prima il formato combinato (orario + nome nella stessa cella)
                orario, cognome = extract_time_and_name(cell_str)
                
                if orario and cognome:
                    # Caso MARTEDÌ: orario e nome nella stessa cella
                    start_time, end_time = orario
                    docente = get_docente_for_slot(aula_nome, start_time, col_letter, ws)
                    
                    lezioni.append({
                        'aula': aula_nome,
                        'ora_inizio': start_time,
                        'ora_fine': end_time,
                        'cognome': cognome,
                        'docente': docente,
                        'materia': materia
                    })
                    
                    print(f"  + {start_time}-{end_time} | {cognome:20} | {docente}")
                
                elif orario and not cognome:
                    # Solo orario, salva per la prossima riga
                    current_time = orario
                
                else:
                    # Potrebbe essere un nome
                    # Cerca solo cognome (parole in maiuscolo)
                    words = cell_str.split()
                    cognome_found = None
                    for word in words:
                        if word.isupper() and len(word) > 2 and 'PROVA' not in word:
                            cognome_found = word
                            break
                    
                    if cognome_found and current_time:
                        # Caso MERCOLEDÌ/altri: orario e nome in celle separate
                        start_time, end_time = current_time
                        docente = get_docente_for_slot(aula_nome, start_time, col_letter, ws)
                        
                        lezioni.append({
                            'aula': aula_nome,
                            'ora_inizio': start_time,
                            'ora_fine': end_time,
                            'cognome': cognome_found,
                            'docente': docente,
                            'materia': materia
                        })
                        
                        print(f"  + {start_time}-{end_time} | {cognome_found:20} | {docente}")
                        current_time = None  # Reset dopo aver usato l'orario
        
        print(f"\nTotale lezioni trovate: {len(lezioni)}")
        
        # Genera SQL
        if len(lezioni) == 0:
            print(f"ATTENZIONE: Nessuna lezione trovata per {giorno_it}!")
            return None
        
        for lez in lezioni:
            sql = f"""INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT '{giorno_it.upper()}', '{lez['ora_inizio']}', '{lez['ora_fine']}',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%{lez['aula']}%' LIMIT 1),
       (SELECT s.id FROM soci s JOIN persone p ON s.persona_id = p.id WHERE UPPER(p.cognome) LIKE '%{lez['cognome']}%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%{lez['docente']}%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%{lez['materia'].split()[0]}%' LIMIT 1);"""
            
            sql_statements.append(sql)
        
        wb.close()
        return '\n\n'.join(sql_statements)
        
    except Exception as e:
        print(f"ERRORE processando {giorno}: {e}")
        import traceback
        traceback.print_exc()
        return None


def main():
    """Funzione principale"""
    giorni = [
        ('MERCOLEDI', 'Mercoledì'),
        ('GIOVEDI', 'Giovedì'),
        ('VENERDI', 'Venerdì'),
        ('SABATO', 'Sabato')
    ]
    
    print("="*60)
    print("GENERAZIONE SQL PER TUTTA LA SETTIMANA")
    print("="*60)
    
    for giorno, giorno_it in giorni:
        sql_content = process_day(giorno, giorno_it)
        
        if sql_content:
            output_file = os.path.join(
                os.path.dirname(__file__), 
                '..', 
                'database', 
                f'insert_lezioni_{giorno}.sql'
            )
            
            with open(output_file, 'w', encoding='utf-8') as f:
                f.write(sql_content)
            
            print(f"OK File salvato: {output_file}\n")
        else:
            print(f"ERRORE: Impossibile generare SQL per {giorno_it}\n")
    
    print("="*60)
    print("COMPLETATO!")
    print("="*60)


if __name__ == '__main__':
    main()