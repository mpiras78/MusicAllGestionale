import openpyxl
import sys
import os

# Percorso del file Excel
excel_file = os.path.join(os.path.dirname(__file__), '..', 'template', 'Orario Allievi MusicAll.xlsx')

# Mapping aule
aule_map = {
    'C': ('MIDI', 'Chitarra'),
    'D': ('PIANO', 'Piano e Canto'),
    'E': ('MAGNA', 'Canto'),
    'F': ('JAZZ', 'Batteria'),
    'G': ('POP', 'Canto'),
    'H': ('ROCK', 'Canto')
}

# Docenti per colonna (dalle righe 6-7)
docenti_map = {
    'C': ['MARCANTE', 'TESSITORE'],
    'D': ['SALVUCCI', 'PACCHIAROTTI'],
    'E': ['DI CRESCE'],
    'F': ['ALBERINI'],
    'G': ['BUONO', 'LORITO'],
    'H': ['MARCANTE', 'DI GIORGIO']
}

try:
    wb = openpyxl.load_workbook(excel_file, data_only=True)
    ws = wb['MARTEDI']
    
    sql_statements = []
    sql_statements.append("-- =============================================")
    sql_statements.append("-- LEZIONI MARTEDÌ")
    sql_statements.append("-- =============================================\n")
    
    # Leggi tutte le celle e estrai le lezioni
    lezioni = []
    
    # Colonne da C a H (3 a 8)
    for col_idx in range(3, 9):
        col_letter = openpyxl.utils.get_column_letter(col_idx)
        aula_nome, materia_default = aule_map.get(col_letter, (None, None))
        
        if not aula_nome:
            continue
            
        print(f"\n=== Processando {aula_nome} (Colonna {col_letter}) ===")
        
        # Determina il docente (dopo le 16:00 può cambiare)
        docente_mattina = docenti_map[col_letter][0] if docenti_map.get(col_letter) else None
        docente_pomeriggio = docenti_map[col_letter][1] if len(docenti_map.get(col_letter, [])) > 1 else docente_mattina
        
        # Scansiona le righe per trovare orari e allievi
        current_orario = None
        
        for row in range(8, 45):
            cell_value = ws.cell(row=row, column=col_idx).value
            
            # Controlla se è un orario nella colonna B
            cell_b = ws.cell(row=row, column=2).value
            if cell_b and isinstance(cell_b, str) and ('-' in cell_b or '/' in cell_b):
                # Non fare nulla, l'orario è nella cella dell'allievo
                pass
            
            if cell_value:
                value_str = str(cell_value).strip()
                
                # Salta celle con note o valori non utili
                if any(skip in value_str.upper() for skip in ['NO', 'PAUSA', 'NOTE', 'DA FARE', 'DOCENTE']):
                    continue
                
                # Se contiene un orario (con / o -)
                if '/' in value_str or '-' in value_str:
                    parts = value_str.split()
                    
                    # Estrai orario
                    orario_part = None
                    allievo_part = None
                    
                    for part in parts:
                        if '/' in part or '-' in part:
                            orario_part = part.replace('/', '-')
                            break
                    
                    # Il resto è l'allievo
                    allievo_part = value_str.replace(orario_part, '').strip() if orario_part else value_str
                    
                    # Pulisci nome allievo
                    allievo_part = allievo_part.replace('(', '').replace(')', '').strip()
                    allievo_parts = allievo_part.split()
                    
                    # Prendi solo il cognome (prima parola in maiuscolo)
                    allievo = None
                    for word in allievo_parts:
                        if word.isupper() and len(word) > 2:
                            allievo = word
                            break
                    
                    if not allievo:
                        continue
                    
                    # Salta prove
                    if 'PROVA' in value_str.upper():
                        continue
                    
                    # Determina docente in base all'orario
                    if orario_part:
                        ora_inizio = orario_part.split('-')[0].strip()
                        ora_num = int(ora_inizio.split(':')[0].split('.')[0])
                        
                        # Dopo le 16:00, usa docente pomeriggio
                        if col_letter == 'D' and ora_num >= 16:
                            docente = 'PACCHIAROTTI'
                            materia = 'Piano e Canto'
                        else:
                            docente = docente_mattina
                            materia = materia_default
                        
                        lezioni.append({
                            'aula': aula_nome,
                            'orario': orario_part,
                            'allievo': allievo,
                            'docente': docente,
                            'materia': materia
                        })
                        
                        print(f"  ✓ {orario_part} - {allievo} ({docente}) - {materia}")
    
    # Genera SQL
    for lez in lezioni:
        orario_parts = lez['orario'].replace('.', ':').split('-')
        ora_inizio = orario_parts[0].strip()
        ora_fine = orario_parts[1].strip() if len(orario_parts) > 1 else ora_inizio
        
        # Assicura formato HH:MM
        if len(ora_inizio.split(':')[0]) == 1:
            ora_inizio = '0' + ora_inizio
        if len(ora_fine.split(':')[0]) == 1:
            ora_fine = '0' + ora_fine
            
        sql = f"""INSERT INTO lezioni (giorno_settimana, ora_inizio, ora_fine, id_aula, id_allievo, id_docente, id_materia)
SELECT 'MARTEDÌ', '{ora_inizio}', '{ora_fine}',
       (SELECT id FROM aule WHERE UPPER(nome) LIKE '%{lez['aula']}%' LIMIT 1),
       (SELECT id FROM allievi WHERE UPPER(cognome) LIKE '%{lez['allievo']}%' LIMIT 1),
       (SELECT id FROM docenti WHERE UPPER(cognome) LIKE '%{lez['docente']}%' LIMIT 1),
       (SELECT id FROM materie WHERE UPPER(nome) LIKE '%{lez['materia'].split()[0]}%' LIMIT 1);"""
        
        sql_statements.append(sql)
    
    # Scrivi file SQL
    output_file = os.path.join(os.path.dirname(__file__), '..', 'database', 'insert_lezioni_MARTEDI.sql')
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write('\n\n'.join(sql_statements))
    
    print(f"\nGenerati {len(lezioni)} INSERT SQL in {output_file}")
    
    wb.close()
    
except Exception as e:
    print(f"Errore: {e}")
    import traceback
    traceback.print_exc()
    sys.exit(1)
