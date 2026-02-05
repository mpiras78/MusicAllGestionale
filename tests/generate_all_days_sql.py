import openpyxl
import sys
import os

# Percorso del file Excel
excel_file = os.path.join(os.path.dirname(__file__), '..', 'template', 'Orario Allievi MusicAll.xlsx')

giorni = ['MERCOLEDI', 'GIOVEDI', 'VENERDI', 'SABATO']

for giorno in giorni:
    try:
        print(f"\n{'='*50}")
        print(f"GENERAZIONE SQL {giorno}")
        print(f"{'='*50}\n")
        
        wb = openpyxl.load_workbook(excel_file, data_only=True)
        
        if giorno not in wb.sheetnames:
            print(f"Foglio {giorno} non trovato!")
            continue
            
        ws = wb[giorno]
        
        sql_statements = []
        sql_statements.append(f"-- =============================================")
        sql_statements.append(f"-- LEZIONI {giorno}")
        sql_statements.append(f"-- Generato automaticamente dall'Excel")
        sql_statements.append(f"-- =============================================\n")
        
        lezioni = []
        
        # Stampa tutta la struttura per capire il layout
        print("Struttura foglio (prime 50 righe):\n")
        for row in range(1, 50):
            row_data = []
            for col in range(2, 9):  # Colonne B-H
                cell = ws.cell(row=row, column=col)
                if cell.value:
                    col_letter = openpyxl.utils.get_column_letter(col)
                    value = str(cell.value).strip()[:40]  # Max 40 char
                    row_data.append(f"{col_letter}{row}:{value}")
            
            if row_data:
                print(f"  {' | '.join(row_data)}")
        
        output_file = os.path.join(os.path.dirname(__file__), '..', 'database', f'insert_lezioni_{giorno}.sql')
        with open(output_file, 'w', encoding='utf-8') as f:
            f.write('\n\n'.join(sql_statements))
        
        print(f"\n✓ File creato: {output_file}")
        print(f"✓ NOTA: Struttura stampata sopra. Rivedere manualmente e popolare SQL.\n")
        
        wb.close()
        
    except Exception as e:
        print(f"ERRORE con {giorno}: {e}")
        import traceback
        traceback.print_exc()

print("\n" + "="*50)
print("COMPLETATO - File SQL creati (da popolare manualmente)")
print("="*50)