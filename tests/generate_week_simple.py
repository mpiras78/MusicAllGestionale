#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script semplificato per generare INSERT SQL leggendo dall'Excel
Mantiene struttura e indentatura leggibile come insert_lezioni_MARTEDI.sql
"""

import openpyxl
import os
import re

excel_file = os.path.join(os.path.dirname(__file__), '..', 'template', 'Orario Soci MusicAll.xlsx')

# Mapping colonne aule
AULE = {
    'C': ('AULA MIDI', 'Chitarra', 'TESSITORE'),
    'D': ('AULA PIANO', 'Piano e Canto', 'SALVUCCI'),
    'E': ('AULA MAGNA', 'Canto', 'DI CRESCE'),
    'F': ('SALA JAZZ', 'Batteria', 'ALBERINI'),
    'G': ('SALA POP', 'Canto', 'BUONO'),
    'H': ('SALA ROCK', 'Canto', 'MARCANTE')
}

def extract_orario(text):
    """Estrae orario dalla cella"""
    if not text:
        return None
    text = str(text).strip()
    # Pattern: 10:00/11:00 o 10.00-11.00 etc
    match = re.search(r'(\d{1,2}[:\.]\d{2})\s*[/-]\s*(\d{1,2}[:\.]\d{2})', text)
    if match:
        start = match.group(1).replace('.', ':')
        end = match.group(2).replace('.', ':')
        # Aggiungi zero padding
        if len(start.split(':')[0]) == 1:
            start = '0' + start
        if len(end.split(':')[0]) == 1:
            end = '0' + end
        return start, end
    return None

def extract_cognome(text):
    """Estrae cognome dalla cella"""
    if not text:
        return None
    text = str(text).strip().upper()
    
    # Skip parole inutili
    if any(x in text for x in ['DOCENTE', 'PAUSA', 'PROVA', 'NOTE', 'DA FARE']):
        return None
    
    # Cerca prima parola in maiuscolo > 2 caratteri
    words = text.split()
    for word in words:
        if word.isupper() and len(word) > 2:
            return word
    return None

def process_giorno(sheet_name, giorno_it):
    """Processa un giorno dell'Excel"""
    print(f"\n{'='*70}")
    print(f"Elaborazione {giorno_it.upper()}")
    print(f"{'='*70}\n")
    
    wb = openpyxl.load_workbook(excel_file, data_only=True)
    
    if sheet_name not in wb.sheetnames:
        print(f"ERRORE: Foglio {sheet_name} non trovato!")
        return None
    
    ws = wb[sheet_name]
    
    output = []
    output.append("-- =============================================")
    output.append(f"-- LEZIONI {giorno_it.upper()}")
    output.append("-- Generato automaticamente - VERIFICARE ORARI")
    output.append("-- =============================================\n")
    
    total_lezioni = 0
    
    # Per ogni aula/sala
    for col_letter in ['C', 'D', 'E', 'F', 'G', 'H']:
        col_idx = ord(col_letter) - ord('A') + 1
        aula_nome, materia, docente_default = AULE[col_letter]
        
        print(f"Scansiono {aula_nome}...")
        
        # Raccogli tutte le lezioni di questa aula
        lezioni = []
        current_orario = None
        
        for row in range(10, 50):
            cell_value = ws.cell(row=row, column=col_idx).value
            
            if not cell_value:
                continue
                
            cell_str = str(cell_value).strip()
            
            # Prova a estrarre orario
            orario = extract_orario(cell_str)
            if orario:
                current_orario = orario
                # Prova anche cognome nella stessa cella (formato MARTEDÌ)
                cognome = extract_cognome(cell_str)
                if cognome:
                    lezioni.append({
                        'orario': orario,
                        'cognome': cognome
                    })
                    current_orario = None
            else:
                # Prova cognome
                cognome = extract_cognome(cell_str)
                if cognome and current_orario:
                    lezioni.append({
                        'orario': current_orario,
                        'cognome': cognome
                    })
                    current_orario = None
        
        if lezioni:
            # Aggiungi separatore per questa aula
            output.append(f"-- {aula_nome} ({materia} - {docente_default})")
            
            for lez in lezioni:
                start, end = lez['orario']
                cognome = lez['cognome']
                
                # Determina materia corretta
                if 'PIANO' in aula_nome:
                    # Per PIANO alterna tra Piano e Canto
                    mat = 'CANTO' if 'PIANO' not in materia else 'PIANO'
                elif 'MIDI' in aula_nome or 'CHITARRA' in materia:
                    mat = 'CHITARRA'
                elif 'JAZZ' in aula_nome or 'BATTERIA' in materia:
                    mat = 'BATTERIA'
                else:
                    mat = 'CANTO'
                
                sql = f"""INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_socio, id_docente, id_materia)
SELECT '{giorno_it.upper()}', '{start}', '{end}',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%{aula_nome.split()[1]}%' LIMIT 1),
       (SELECT s.id FROM soci s JOIN persone p ON s.persona_id = p.id WHERE UPPER(p.cognome) LIKE '%{cognome}%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%{docente_default}%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%{mat}%' LIMIT 1);"""
                
                output.append(sql)
                total_lezioni += 1
                print(f"  + {start}-{end} | {cognome:20}")
            
            output.append("")  # Riga vuota tra aule
    
    wb.close()
    
    print(f"\nTotale lezioni: {total_lezioni}")
    
    if total_lezioni == 0:
        return None
    
    return '\n'.join(output)

def main():
    giorni = [
        ('MERCOLEDI', 'Mercoledì'),
        ('GIOVEDI', 'Giovedì'),
        ('VENERDI', 'Venerdì'),
        ('SABATO', 'Sabato')
    ]
    
    for sheet_name, giorno_it in giorni:
        sql_content = process_giorno(sheet_name, giorno_it)
        
        if sql_content:
            output_file = os.path.join(
                os.path.dirname(__file__),
                '..',
                'database',
                f'insert_lezioni_{sheet_name}.sql'
            )
            
            with open(output_file, 'w', encoding='utf-8') as f:
                f.write(sql_content)
            
            print(f"OK Salvato: {output_file}\n")
        else:
            print(f"ERRORE: Nessuna lezione trovata per {giorno_it}\n")
    
    print("\n" + "="*70)
    print("COMPLETATO! Verifica e correggi gli orari se necessario.")
    print("="*70)

if __name__ == '__main__':
    main()