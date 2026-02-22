import sys

with open('calendario.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Sostituisci icone Bootstrap con emoji
replacements = [
    ("'bi-music-note-beamed'", "'🎸'"),
    ("'bi-piano'", "'🎹'"),
    ("'bi-mic'", "'🎤'"),
    ("'bi-disc'", "'🥁'"),
    ("'bi-soundwave'", "'🎸'"),
    ("'bi-violin'", "'🎻'"),
    ("'bi-trumpet'", "'🎷'"),
    ("'bi-music-note'", "'🎵'"),
    ("'bi-person-workspace'", "'👥'"),
    ("'bi-mortarboard'", "'👨🏫'"),
    ("'bi-person-x'", "'🚪'"),
    ('<i class="bi <?= $icona ?> icona-strumento"></i>', '<span class="icona-strumento" style="font-size: 1.2rem;"><?= $icona ?></span>'),
    ('<i class="bi <?= $icona_evt ?> icona-strumento"></i>', '<span class="icona-strumento" style="font-size: 1.2rem;"><?= $icona_evt ?></span>'),
    ('<i class="<?= $icona_prenotazione ?> prenotazione-tipo-icon <?= $classe_icona_pren ?>"></i>', '<span class="prenotazione-tipo-icon <?= $classe_icona_pren ?>" style="font-size: 1.1rem;"><?= $icona_prenotazione ?></span>'),
    ('<i class="<?= $icona_prenotazione_evt ?> prenotazione-tipo-icon <?= $classe_icona_pren_evt ?>"></i>', '<span class="prenotazione-tipo-icon <?= $classe_icona_pren_evt ?>" style="font-size: 1.1rem;"><?= $icona_prenotazione_evt ?></span>'),
]

for old, new in replacements:
    content = content.replace(old, new)

with open('calendario.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Icone sostituite con emoji!")
