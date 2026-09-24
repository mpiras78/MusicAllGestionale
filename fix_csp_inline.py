#!/usr/bin/env python3
"""
Script per aggiungere automaticamente il nonce a tutti gli script inline
e per rimuovere gli inline event handlers (onclick, onchange, etc)
"""

import re
import sys
from pathlib import Path

def add_nonce_to_scripts(file_path):
    """Legge un file PHP e aggiunge il nonce ai tag <script> inline"""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original_content = content
        
        # 1. Aggiungi nonce ai <script> inline (non esterni)
        # Pattern: <script> (ma non <script src= o <script type= ecc)
        # Sostituisci con: <script nonce="<?= $_SESSION['csp_nonce'] ?>">
        content = re.sub(
            r'<script>\n',
            '<script nonce="<?= $_SESSION[\'csp_nonce\'] ?>">\n',
            content
        )
        
        # 2. Rimuovi inline event handlers
        # Rimuovi: onclick="...", onchange="...", ecc
        inline_handlers = ['onclick', 'onchange', 'onload', 'onerror', 'onsubmit', 'onreset']
        
        for handler in inline_handlers:
            # Pattern: handler="..."
            pattern = f' {handler}="[^"]*"'
            content = re.sub(pattern, '', content)
            
            # Anche single quotes
            pattern = f" {handler}='[^']*'"
            content = re.sub(pattern, '', content)
        
        # Se il contenuto è cambiato, scrivi il file
        if content != original_content:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
        
    except Exception as e:
        print(f"Errore in {file_path}: {e}")
        return False

def main():
    # Trovi tutti i file PHP nella directory
    base_dir = Path('.')
    php_files = list(base_dir.glob('**/*.php'))
    
    modified_files = []
    
    for php_file in php_files:
        # Salta i file di test
        if 'test' in str(php_file).lower() or 'vendor' in str(php_file):
            continue
            
        if add_nonce_to_scripts(php_file):
            modified_files.append(str(php_file))
            print(f"✅ Modificato: {php_file}")
        else:
            print(f"⏭️  Non modificato: {php_file}")
    
    print(f"\n{'='*60}")
    print(f"Riepilogo: {len(modified_files)} file modificati")
    if modified_files:
        print("\nFile modificati:")
        for f in modified_files:
            print(f"  - {f}")

if __name__ == '__main__':
    main()
