"""
Remove the 5 consecutive empty paragraphs on the cover page (between subtitle
"Comprehensive Technical Documentation" and the closing note paragraph).
Also remove any other consecutive empty paragraphs > 2 in a row.
"""
from docx import Document
from docx.oxml.ns import qn
import os

IN_PATH = r"C:\MAGZANIV6\Magzani\docs\Magzani_ERP_Full_Documentation.docx"
doc = Document(IN_PATH)
body = doc.element.body

# Walk through paragraphs in document order
# Track consecutive empty paragraphs.
# When we have > 2 consecutive empties, delete the excess.
empties = []  # list of (index, paragraph element) for current empty streak
total_removed = 0

def is_visible(p_elem):
    """A paragraph is visible if it has any text content."""
    text_elements = p_elem.findall(qn('w:r'))
    for r in text_elements:
        # Check text runs
        t_elements = r.findall(qn('w:t'))
        for t in t_elements:
            if (t.text or '').strip():
                return True
    # Check if there's a table inside? Tables are siblings of paragraphs, not children.
    # Also consider an explicit break.
    for br in p_elem.iter(qn('w:br')):
        # If it has any explicit break, treat as not-blank (some content)
        br_type = br.get(qn('w:type'))
        if br_type == 'page':
            return True
    return False

paragraphs = body.findall(qn('w:p'))  # only paragraphs (not tables)
prev_was_blank = False
to_delete = []
blank_streak_count = 0
blank_streak_paras = []

for p_elem in paragraphs:
    visible = is_visible(p_elem)
    if not visible:
        blank_streak_count += 1
        blank_streak_paras.append(p_elem)
    else:
        # End of blank streak — keep only first 2 blanks if more than 2
        if blank_streak_count > 2:
            # Mark the excess blanks for deletion (keep only first 2 in this streak)
            for extra in blank_streak_paras[2:]:
                to_delete.append(extra)
                total_removed += 1
        blank_streak_count = 0
        blank_streak_paras = []

# Final streak (might be at the end - keep)
if blank_streak_count > 2:
    for extra in blank_streak_paras[2:]:
        to_delete.append(extra)
        total_removed += 1

for p in to_delete:
    body.remove(p)

print(f"Removed {total_removed} excess empty paragraphs")
doc.save(IN_PATH)
print(f"Saved: {IN_PATH}")
print(f"Size: {os.path.getsize(IN_PATH) / 1024:.1f} KB")
