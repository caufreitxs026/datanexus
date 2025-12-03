import pandas as pd
import calendar
import locale
import sys
import os
import json
import warnings

# Configuração de Encoding e Locale
sys.stdout.reconfigure(encoding='utf-8')
warnings.simplefilter("ignore")

try:
    locale.setlocale(locale.LC_TIME, 'pt_BR.utf8')
except:
    try:
        locale.setlocale(locale.LC_TIME, 'pt_BR')
    except:
        pass

if len(sys.argv) < 3:
    print(json.dumps({"error": "Insufficient arguments."}))
    sys.exit()

INPUT_FILE = sys.argv[1]
OUTPUT_DIR = sys.argv[2]

# Estrutura de Resposta
response_data = {
    "files_generated": [],
    "datasets": {
        "MonthView": {"type": "Standard", "rows": [], "total": 0.0},
        "CycleView": {"type": "Standard", "rows": [], "total": 0.0},
        "SpecialView": {"type": "Special", "rows": [], "total": 0.0}
    },
    "errors": []
}

def process_sheet(sheet_name, config):
    try:
        # Verifica se aba existe
        df_check = pd.read_excel(INPUT_FILE, sheet_name=None)
        if sheet_name not in df_check: return

        # Leitura de Datas
        df_dates = pd.read_excel(INPUT_FILE, sheet_name=sheet_name, header=None, nrows=config['header_rows'])
        
        if config['header_rows'] == 1:
            ref_date = pd.to_datetime(df_dates.iloc[0, 1], dayfirst=True, errors='coerce')
            if pd.isna(ref_date): return
            start_date = ref_date.replace(day=1)
            last_day = calendar.monthrange(ref_date.year, ref_date.month)[1]
            end_date = ref_date.replace(day=last_day)
            
            str_start, str_end = start_date.strftime('%d/%m/%Y'), end_date.strftime('%d/%m/%Y')
            month_name = ref_date.strftime('%B').capitalize()
            description = f"{config['desc_prefix']} - 01 to {last_day} - {month_name}"
        else:
            d_start = pd.to_datetime(df_dates.iloc[0, 1], dayfirst=True, errors='coerce')
            d_end = pd.to_datetime(df_dates.iloc[1, 1], dayfirst=True, errors='coerce')
            if pd.isna(d_start): return
            str_start, str_end = d_start.strftime('%d/%m/%Y'), d_end.strftime('%d/%m/%Y')
            m_start, m_end = d_start.strftime('%b').capitalize(), d_end.strftime('%b').capitalize()
            description = f"{config['desc_prefix']} - Cycle - {m_start}/{m_end}"

        # Leitura de Dados
        df = pd.read_excel(INPUT_FILE, sheet_name=sheet_name, header=3)
        fixed_cols = ['Supervisor_ID', 'Salesperson_ID']
        target_cols = [c for c in config['columns'] if c in df.columns]
        
        df = df[fixed_cols + target_cols].fillna(0.0)
        
        # Unpivot (Melt)
        df_melted = df.melt(id_vars=fixed_cols, value_vars=target_cols, var_name='Product_Line', value_name='Value')
        
        # Enrich Data
        df_melted['Target_Type'] = config['type']
        df_melted['Start_Date'] = str_start
        df_melted['End_Date'] = str_end
        df_melted['Description'] = description
        
        # Prepare Export
        export_df = df_melted.rename(columns={'Supervisor_ID': 'supervisor_id', 'Salesperson_ID': 'salesperson_id', 'Product_Line': 'product_line', 'Value': 'value'})
        
        # Export CSV
        file_name = f"import_{config['file_suffix']}.txt"
        file_path = os.path.join(OUTPUT_DIR, file_name)
        export_df.to_csv(file_path, sep=';', index=False, encoding='utf-8-sig', float_format='%.2f')

        # Prepare JSON Response
        records = export_df.to_dict(orient='records')
        for rec in records:
            rec['value'] = float(rec['value'])
            rec['supervisor_id'] = int(rec['supervisor_id']) if pd.notna(rec['supervisor_id']) else 0
            rec['salesperson_id'] = int(rec['salesperson_id']) if pd.notna(rec['salesperson_id']) else 0

        total_val = float(export_df['value'].sum())

        response_data['files_generated'].append(file_name)
        response_data['datasets'][config['key']]['rows'] = records
        response_data['datasets'][config['key']]['total'] = total_val

    except Exception as e:
        response_data['errors'].append(f"Sheet {sheet_name}: {str(e)}")

# Configurações de Negócio
configs = [
    {'key': 'MonthView', 'sheet': '01a31', 'type': 'Standard', 'desc_prefix': 'Target STD', 'header_rows': 1, 'file_suffix': 'std_month', 'columns': ['Category A', 'Category B', 'Category C', 'Category D', 'Category E']},
    {'key': 'CycleView', 'sheet': '20a19', 'type': 'Standard', 'desc_prefix': 'Target STD', 'header_rows': 2, 'file_suffix': 'std_cycle', 'columns': ['Category A', 'Category B', 'Category C', 'Category E']},
    {'key': 'SpecialView', 'sheet': 'Cob', 'type': 'Special', 'desc_prefix': 'Target SPC', 'header_rows': 1, 'file_suffix': 'spc_month', 'columns': ['Category A', 'Category B', 'Category C', 'Category D', 'Category E']}
]

try:
    for cfg in configs:
        process_sheet(cfg['sheet'], cfg)
    print(json.dumps(response_data))
except Exception as e:
    print(json.dumps({"error": f"Fatal Error: {str(e)}"}))