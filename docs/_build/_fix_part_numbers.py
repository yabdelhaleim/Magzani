"""
Post-build fix: Rename the duplicate "Part 2" headings so they are unique.
Part 2: Application Architecture → keep as is
Part 2: Tech Stack → rename to Part 3: Tech Stack
Part 3: Complete File Tree → Part 4
Part 4: Modules (Deep Dive) → Part 5
Part 5: Complete Routes Reference → Part 6
Part 6: Business Logic Deep-Dive → Part 7
Part 7: Recent Changes & Known Issues → Part 8
Part 8: Quick Reference Card → Part 9
"""
from docx import Document
import os, sys

IN_PATH = r"C:\MAGZANIV6\Magzani\docs\Magzani_ERP_Full_Documentation.docx"

doc = Document(IN_PATH)

renames = [
    ('Part 2: Tech Stack',                                 'Part 3: Tech Stack'),
    ('Part 3: Complete File Tree',                         'Part 4: Complete File Tree'),
    ('Part 4: Modules (Deep Dive)',                        'Part 5: Modules (Deep Dive)'),
    ('Part 5: Complete Routes Reference',                 'Part 6: Complete Routes Reference'),
    ('Part 6: Business Logic Deep-Dive',                   'Part 7: Business Logic Deep-Dive'),
    ('Part 7: Recent Changes & Known Issues',              'Part 8: Recent Changes & Known Issues'),
    ('Part 8: Quick Reference Card',                       'Part 9: Quick Reference Card'),
]

# We need to do this bottom-up to avoid side effects if any logic depends on order.
# Actually python-docx finds by exact text replacement, so order doesn't matter much.
# But to be safe, let's do it in reverse.
count = 0
for old, new in reversed(renames):
    for p in doc.paragraphs:
        if p.text.strip() == old:
            for run in p.runs:
                run.text = ''
            # set first run to new text
            if p.runs:
                p.runs[0].text = new
            count += 1
            print(f"Renamed: {old} → {new}")
            break

doc.save(IN_PATH)
print(f"\n{count} heading renames applied.")
print(f"Saved to: {IN_PATH}")
print(f"Size: {os.path.getsize(IN_PATH) / 1024:.1f} KB")
