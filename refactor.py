import os
import re

files_to_process = [
    'notifications.php',
    'settings.php',
    'profile_update.php',
    'passwordReset.php',
    'passwordChange.php',
    'overdue_report.php',
    'graph.php',
    'changePassword.php'
]

base_dir = r"C:\xampp\htdocs\Connect-Amravati-Zeal-FIP"

for filename in files_to_process:
    filepath = os.path.join(base_dir, filename)
    if not os.path.exists(filepath):
        continue
    
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    if '<!DOCTYPE html>' not in content:
        continue
        
    print(f"Processing {filename}...")
    
    # Extract title
    title_match = re.search(r'<title>(.*?)</title>', content, re.IGNORECASE | re.DOTALL)
    page_title = title_match.group(1).strip() if title_match else filename
    # Remove htmlspecialchars if it exists
    page_title = page_title.replace("<?= htmlspecialchars($t['title']) ?>", "$t['title']")
    
    # Extract style
    style_match = re.search(r'<style>(.*?)</style>', content, re.IGNORECASE | re.DOTALL)
    style_content = style_match.group(1) if style_match else ""
    
    # Guess active page
    active_page = filename.replace('.php', '')
    if active_page == 'profile_update' or 'password' in active_page.lower():
        active_page = 'settings'
        
    # Find the bounds
    doctype_idx = content.find('<!DOCTYPE html>')
    aside_end_match = re.search(r'</aside>', content, re.IGNORECASE)
    if not aside_end_match:
        print(f"  Skipping {filename}: no </aside> found")
        continue
        
    aside_end_idx = aside_end_match.end()
    
    extra_head_str = ""
    if style_content.strip():
        extra_head_str = f"""$extraHead = <<<'EOT'
    <style>{style_content}</style>
EOT;"""
    
    title_assignment = f"$pageTitle = {page_title};" if "$t['title']" in page_title else f"$pageTitle = '{page_title}';"
    
    replacement = f"""<?php
{title_assignment}
{extra_head_str}
include 'include/header.php';
$activePage = '{active_page}';
include 'include/sidebar.php';
?>"""
    
    new_content = content[:doctype_idx] + replacement + content[aside_end_idx:]
    
    # Replace the footer
    new_content = re.sub(r'</body>\s*</html>', "<?php include 'include/footer.php'; ?>\n", new_content, flags=re.IGNORECASE)
    
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(new_content)
        
    print(f"  Done {filename}")
