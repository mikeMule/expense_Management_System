import re

file_path = r'c:\xampp\htdocs\expense_Management_System-v01\expense_Management_System-v2\BackupDB\masterDB4.sql'

def fix_line(line):
    if not line.strip().startswith('('):
        return line
        
    # Find the content of the tuple
    start = line.find('(')
    end = line.rfind(')')
    if start == -1 or end == -1:
        return line
        
    prefix = line[:start+1]
    suffix = line[end:]
    content = line[start+1:end]
    
    # Split into values
    values = []
    current = ""
    in_str = False
    escaped = False
    for char in content:
        if char == "'" and not escaped:
            in_str = not in_str
            current += char
        elif char == "\\" and not escaped:
            escaped = True
            current += char
        elif char == "," and not in_str:
            values.append(current.strip())
            current = ""
        else:
            current += char
            escaped = False
    values.append(current.strip())
    
    # 1. Ensure 11 columns for transactions
    if len(values) == 10:
        values.append("'Addis Ababa'")
    
    # 2. Fix escaping in each string value
    fixed_values = []
    for i, v in enumerate(values):
        if v.startswith("'") and v.endswith("'"):
            inner = v[1:-1]
            # Unescape first to avoid triple escaping, then escape all '
            inner = inner.replace("\\'", "'")
            # Now escape every '
            inner = inner.replace("'", "\\'")
            fixed_values.append("'" + inner + "'")
        else:
            fixed_values.append(v)
            
    return prefix + ", ".join(fixed_values) + suffix

with open(file_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

new_lines = []
is_trans = False
for line in lines:
    if "INSERT INTO `transactions`" in line:
        is_trans = True
        new_lines.append(line)
        continue
    
    if is_trans:
        new_line = fix_line(line)
        new_lines.append(new_line)
        if line.strip().endswith(';'):
            is_trans = False
        continue
        
    new_lines.append(line)

with open(file_path, 'w', encoding='utf-8') as f:
    f.writelines(new_lines)
