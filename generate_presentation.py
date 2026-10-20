import os
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.enum.text import PP_ALIGN
from pptx.dml.color import RGBColor
from pptx.enum.shapes import MSO_SHAPE

def create_deck():
    prs = Presentation()
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    blank_layout = prs.slide_layouts[6]

    # Color Palette
    C_NAVY_DARK = RGBColor(15, 23, 42)      # #0f172a
    C_NAVY_CARD = RGBColor(30, 41, 59)      # #1e293b
    C_BLUE = RGBColor(37, 99, 235)          # #2563eb
    C_BLUE_LIGHT = RGBColor(239, 246, 255)  # #eff6ff
    C_GREEN = RGBColor(16, 185, 129)        # #10b981
    C_AMBER = RGBColor(245, 158, 11)        # #f59e0b
    C_RED = RGBColor(239, 68, 68)           # #ef4444
    C_TEXT_DARK = RGBColor(15, 23, 42)
    C_TEXT_MUTED = RGBColor(100, 116, 139)  # #64748b
    C_WHITE = RGBColor(255, 255, 255)
    C_BG_LIGHT = RGBColor(248, 250, 252)    # #f8fafc
    C_BORDER_LIGHT = RGBColor(226, 232, 240)# #e2e8f0

    logo_path = "/home/zainal/Documents/Digipen/public/images/logo-didispen.png"
    has_logo = os.path.exists(logo_path)

    def add_header(slide, title, category="SISTEM DISPENSASI SISWA DIGITAL (DIDISPEN)"):
        # Header banner shape
        header_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.4), Inches(11.7), Inches(1.1))
        tf = header_box.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0
        
        p0 = tf.paragraphs[0]
        p0.text = category.upper()
        p0.font.size = Pt(10)
        p0.font.bold = True
        p0.font.color.rgb = C_BLUE
        
        p1 = tf.add_paragraph()
        p1.text = title
        p1.font.size = Pt(22)
        p1.font.bold = True
        p1.font.color.rgb = C_NAVY_DARK

        # Top-right small badge/logo
        if has_logo:
            try:
                slide.shapes.add_picture(logo_path, Inches(11.8), Inches(0.4), height=Inches(0.7))
            except Exception:
                pass

    def add_card(slide, left, top, width, height, bg_color=C_WHITE, border_color=C_BORDER_LIGHT):
        shape = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height)
        shape.fill.solid()
        shape.fill.fore_color.rgb = bg_color
        if border_color:
            shape.line.color.rgb = border_color
            shape.line.width = Pt(1.5)
        else:
            shape.line.fill.background()
        return shape

    # =========================================================================
    # SLIDE 1: COVER
    # =========================================================================
    slide1 = prs.slides.add_slide(blank_layout)
    bg1 = slide1.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, Inches(13.333), Inches(7.5))
    bg1.fill.solid()
    bg1.fill.fore_color.rgb = C_NAVY_DARK
    bg1.line.fill.background()

    # Decorative accent bar
    bar = slide1.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(1.2), Inches(1.5), Inches(0.15), Inches(4.5))
    bar.fill.solid()
    bar.fill.fore_color.rgb = C_BLUE
    bar.line.fill.background()

    tb1 = slide1.shapes.add_textbox(Inches(1.6), Inches(1.5), Inches(10.5), Inches(4.5))
    tf1 = tb1.text_frame
    tf1.word_wrap = True

    p = tf1.paragraphs[0]
    p.text = "SMK NEGERI 1 BANGSRI • TAHUN AJARAN 2026/2027"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_BLUE

    p = tf1.add_paragraph()
    p.text = "SOSIALISASI OPERASIONAL & FITUR\nDIDISPEN (DISPENSASI DIGITAL)"
    p.font.size = Pt(36)
    p.font.bold = True
    p.font.color.rgb = C_WHITE
    p.space_after = Pt(18)

    p = tf1.add_paragraph()
    p.text = "Panduan Lengkap Alur Kerja & Fitur Unggulan untuk Guru Piket & Petugas Satpam Gerbang"
    p.font.size = Pt(16)
    p.font.color.rgb = RGBColor(203, 213, 225)
    p.space_after = Pt(28)

    p = tf1.add_paragraph()
    p.text = "Dikembangkan oleh Tim By 3M (Maulana Fahri Oktavian • Muhammad Sabrian Nuh • Muhammad Zainal Arief)"
    p.font.size = Pt(11)
    p.font.color.rgb = C_TEXT_MUTED

    # =========================================================================
    # SLIDE 2: LATAR BELAKANG & TUJUAN
    # =========================================================================
    slide2 = prs.slides.add_slide(blank_layout)
    add_header(slide2, "Mengapa Harus Berpindah ke DIDISPEN?", "TRANSFORMASI DIGITAL SEKOLAH")

    # Card 1: Masalah Kertas Lama
    add_card(slide2, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), C_WHITE, RGBColor(254, 202, 202))
    tb = slide2.shapes.add_textbox(Inches(1.1), Inches(2.0), Inches(5.0), Inches(4.6))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "🔴 KENDALA SISTEM KERTAS LAMA"
    p.font.size = Pt(15)
    p.font.bold = True
    p.font.color.rgb = C_RED
    p.space_after = Pt(12)

    points_lama = [
        "Surat Izin Kertas Mudah Hilang / Tercecer dan sulit diarsipkan.",
        "Potensi 'Joki Izin': Siswa menitipkan surat izin atau memakai nama teman.",
        "Meja Piket & Gerbang Satpam Terisolasi (Satpam tidak tahu apakah izin sah atau palsu).",
        "Siswa Terlambat / Kabur Tidak Terdeteksi karena tidak ada rekap batas jam kembali.",
        "Rekapitulasi Manual Memakan Waktu saat evaluasi BK & Kurikulum bulanan."
    ]
    for pt in points_lama:
        p = tf.add_paragraph()
        p.text = "• " + pt
        p.font.size = Pt(12)
        p.font.color.rgb = C_TEXT_DARK
        p.space_after = Pt(8)

    # Card 2: Solusi DIDISPEN
    add_card(slide2, Inches(6.8), Inches(1.8), Inches(5.7), Inches(5.0), C_WHITE, RGBColor(167, 243, 208))
    tb = slide2.shapes.add_textbox(Inches(7.1), Inches(2.0), Inches(5.1), Inches(4.6))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "🟢 SOLUSI MODERN DIDISPEN"
    p.font.size = Pt(15)
    p.font.bold = True
    p.font.color.rgb = C_GREEN
    p.space_after = Pt(12)

    points_baru = [
        "100% Paperless & Real-Time: Data tersinkronisasi detik itu juga dari meja piket ke gerbang.",
        "Verifikasi Wajah Ganda: Wajib foto selfie siswa saat pengajuan + foto di pos gerbang.",
        "QR Code Dinamis 64-Char: Sekali pakai (single-use), tidak bisa disalin / di-screenshot.",
        "Auto-Overdue Detector: Satpam & Guru otomatis tahu siswa yang terlambat kembali.",
        "Fleksibilitas Struk Thermal: Cetak struk mini 58mm Bluetooth jika siswa tidak memegang HP."
    ]
    for pt in points_baru:
        p = tf.add_paragraph()
        p.text = "✔ " + pt
        p.font.size = Pt(12)
        p.font.color.rgb = C_TEXT_DARK
        p.space_after = Pt(8)

    # =========================================================================
    # SLIDE 3: GAMBARAN BESAR ALUR KERJA (END-TO-END)
    # =========================================================================
    slide3 = prs.slides.add_slide(blank_layout)
    add_header(slide3, "Gambaran Besar Alur Kerja Dispensasi (4 Langkah)", "ALUR OPERASIONAL UTAMA")

    steps = [
        ("LANGKAH 1", "Siswa Mengajukan", "Status: Menunggu", C_AMBER, [
            "Siswa isi form di HP (kategori, jam, alasan).",
            "Wajib foto selfie wajah.",
            "Bisa pengajuan rombongan (lomba/ekskul).",
            "Pilihan: Menemui guru piket di meja piket."
        ]),
        ("LANGKAH 2", "Guru Piket ACC", "Status: Disetujui", C_BLUE, [
            "Guru cek bukti & foto siswa.",
            "Klik 'Setujui' ➔ QR Token terbit.",
            "Opsi cetak Struk Thermal 58mm Bluetooth.",
            "Atau klik 'Tolak' (wajib isi alasan)."
        ]),
        ("LANGKAH 3", "Satpam Gerbang Keluar", "Status: Keluar", C_NAVY_DARK, [
            "Siswa tunjukkan QR di HP/struk.",
            "Satpam scan QR via kamera pos.",
            "Satpam jepret foto siswa di pos.",
            "Klik 'Konfirmasi Keluar' & buka gerbang."
        ]),
        ("LANGKAH 4", "Satpam Gerbang Kembali", "Status: Selesai", C_GREEN, [
            "Siswa kembali lapor ke pos satpam.",
            "Satpam scan ulang QR Code siswa.",
            "Layar Hijau = Tepat Waktu.",
            "Layar Merah = Terlambat (Overdue) + Alert WA."
        ])
    ]

    col_w = Inches(2.75)
    gap = Inches(0.25)
    top_pos = Inches(1.8)
    h_pos = Inches(5.0)

    for i, (tag, title, badge, color, items) in enumerate(steps):
        l = Inches(0.8) + i * (col_w + gap)
        add_card(slide3, l, top_pos, col_w, h_pos, C_WHITE, color)
        
        tb = slide3.shapes.add_textbox(l + Inches(0.15), top_pos + Inches(0.2), col_w - Inches(0.3), h_pos - Inches(0.4))
        tf = tb.text_frame
        tf.word_wrap = True
        
        p = tf.paragraphs[0]
        p.text = tag
        p.font.size = Pt(10)
        p.font.bold = True
        p.font.color.rgb = color
        
        p = tf.add_paragraph()
        p.text = title
        p.font.size = Pt(14)
        p.font.bold = True
        p.font.color.rgb = C_NAVY_DARK
        p.space_after = Pt(6)

        p = tf.add_paragraph()
        p.text = badge
        p.font.size = Pt(10)
        p.font.bold = True
        p.font.color.rgb = color
        p.space_after = Pt(12)

        for item in items:
            p = tf.add_paragraph()
            p.text = "• " + item
            p.font.size = Pt(11)
            p.font.color.rgb = C_TEXT_DARK
            p.space_after = Pt(6)

    # =========================================================================
    # SLIDE 4: SIKLUS 7 STATUS DISPENSASI (STATE MACHINE)
    # =========================================================================
    slide4 = prs.slides.add_slide(blank_layout)
    add_header(slide4, "Siklus Hidup 7 Status Dispensasi (State Machine)", "TRANSPARANSI STATUS")

    statuses = [
        ("1. MENUNGGU", C_AMBER, "Siswa telah mengajukan izin. Menunggu approval verifikasi Guru Piket aktif."),
        ("2. DISETUJUI", C_BLUE, "Guru piket telah menyetujui. QR Token 64-char terbit dan siap dibawa ke gerbang."),
        ("3. DITOLAK", C_RED, "Ditolak guru piket dengan catatan alasan resmi yang terkirim ke siswa."),
        ("4. KELUAR", RGBColor(14, 165, 233), "Satpam memindai QR di gerbang. Waktu keluar aktual tercatat, siswa di luar."),
        ("5. SELESAI", C_GREEN, "Siswa kembali ke pos satpam, discan masuk. Tercatat tepat waktu atau overdue."),
        ("6. KADALUARSA", C_TEXT_MUTED, "Pengajuan yang tidak sempat diproses guru hingga jam KBM usai otomatis hangus."),
        ("7. DIBATALKAN", RGBColor(148, 163, 184), "Siswa membatalkan izin sendiri atau batal keluar sebelum scan gerbang.")
    ]

    for idx, (st_name, st_color, st_desc) in enumerate(statuses):
        row = idx // 2
        col = idx % 2
        w = Inches(5.6) if idx < 6 else Inches(11.6)
        l = Inches(0.8) if col == 0 else Inches(6.8)
        t = Inches(1.8) + row * Inches(1.2)
        h = Inches(1.05)

        add_card(slide4, l, t, w, h, C_WHITE, st_color)
        tb = slide4.shapes.add_textbox(l + Inches(0.2), t + Inches(0.12), w - Inches(0.4), h - Inches(0.24))
        tf = tb.text_frame
        tf.word_wrap = True

        p = tf.paragraphs[0]
        p.text = st_name
        p.font.size = Pt(12)
        p.font.bold = True
        p.font.color.rgb = st_color

        p = tf.add_paragraph()
        p.text = st_desc
        p.font.size = Pt(10)
        p.font.color.rgb = C_TEXT_DARK

    # =========================================================================
    # SLIDE 5: FITUR UNGGULAN SISWA
    # =========================================================================
    slide5 = prs.slides.add_slide(blank_layout)
    add_header(slide5, "Fitur Unggulan Siswa: Mandiri, Cerdas, & Anti-Kecurangan", "MODUL SISWA")

    siswa_features = [
        ("🕒 Terintegrasi Jadwal KBM", "Jam izin disesuaikan langsung dengan jam pelajaran aktif (Jam Ke-1 s/d Ke-10) sehingga tidak ada waktu izin yang asal-asalan."),
        ("👥 Pengajuan Rombongan", "Cukup 1 perwakilan mengajukan izin dan memilih teman anggota kelompoknya untuk keperluan dinas lomba atau kegiatan ekstrakurikuler."),
        ("🔒 Anti-Double Request", "Siswa tidak bisa mengajukan izin baru bila masih memiliki izin aktif yang belum selesai. Menghindari duplikasi permohonan."),
        ("📸 Selfie Verifikasi Wajah", "Wajib melampirkan foto selfie langsung dari kamera perangkat saat kirim form guna mencocokkan identitas fisik siswa di pos satpam."),
        ("📲 Quick Call WhatsApp Piket", "Tombol cepat menghubungi Guru Piket via WhatsApp selama 3 menit pertama setelah mengajukan jika memerlukan respon kilat."),
        ("🎫 Tiket QR Dinamis 64-Bit", "QR Code dengan token terenkripsi acak 64 karakter sekali pakai (single-use) yang tidak bisa dipalsukan atau di-screenshot ulang.")
    ]

    for idx, (title, desc) in enumerate(siswa_features):
        r = idx // 3
        c = idx % 3
        l = Inches(0.8) + c * Inches(3.95)
        t = Inches(1.8) + r * Inches(2.45)
        w = Inches(3.75)
        h = Inches(2.25)

        add_card(slide5, l, t, w, h, C_WHITE, C_BLUE)
        tb = slide5.shapes.add_textbox(l + Inches(0.2), t + Inches(0.2), w - Inches(0.4), h - Inches(0.4))
        tf = tb.text_frame
        tf.word_wrap = True

        p = tf.paragraphs[0]
        p.text = title
        p.font.size = Pt(13)
        p.font.bold = True
        p.font.color.rgb = C_BLUE
        p.space_after = Pt(8)

        p = tf.add_paragraph()
        p.text = desc
        p.font.size = Pt(11)
        p.font.color.rgb = C_TEXT_DARK

    # =========================================================================
    # SLIDE 6: FITUR GURU PIKET (1) - OTORISASI SHIFT & MODE PEMANTAU
    # =========================================================================
    slide6 = prs.slides.add_slide(blank_layout)
    add_header(slide6, "Fitur Guru Piket: Otorisasi Shift Sesi & Mode Pemantau", "MODUL GURU PIKET")

    add_card(slide6, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), C_WHITE, C_GREEN)
    tb = slide6.shapes.add_textbox(Inches(1.1), Inches(2.0), Inches(5.0), Inches(4.6))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "🟢 GURU PIKET AKTIF BERTUGAS"
    p.font.size = Pt(15)
    p.font.bold = True
    p.font.color.rgb = C_GREEN
    p.space_after = Pt(12)

    pts_aktif = [
        "Mendapatkan Badge 'Sedang Bertugas Aktif' pada sesi jam kerja berjalan (Sesi 1 / Sesi 2).",
        "Akses Penuh Verifikasi: Tombol 'Setujui' dan 'Tolak' aktif untuk memproses antrean siswa.",
        "Penerbitan Struk: Berhak mencetak struk thermal 58mm atau mengunduh struk PDF resmi.",
        "Pembuatan Manual: Berhak membuka form 'Dispensasi Mandiri Guru' yang langsung ter-ACC.",
        "Penanganan Rollover: Berhak memproses pengajuan tertunda dari sesi sebelumnya jika belum sempat di-ACC."
    ]
    for pt in pts_aktif:
        p = tf.add_paragraph()
        p.text = "✔ " + pt
        p.font.size = Pt(12)
        p.font.color.rgb = C_TEXT_DARK
        p.space_after = Pt(8)

    add_card(slide6, Inches(6.8), Inches(1.8), Inches(5.7), Inches(5.0), C_WHITE, C_TEXT_MUTED)
    tb = slide6.shapes.add_textbox(Inches(7.1), Inches(2.0), Inches(5.1), Inches(4.6))
    tf = tb.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = "👁️ MODE PEMANTAU (GURU NON-PIKET)"
    p.font.size = Pt(15)
    p.font.bold = True
    p.font.color.rgb = C_NAVY_DARK
    p.space_after = Pt(12)

    pts_pantau = [
        "Status Read-Only: Guru yang sedang mengajar di kelas masuk ke mode pantau secara transparan.",
        "Tombol Eksekusi Terkunci: Tombol Setuju/Tolak dan Buat Pengajuan disembunyikan/dinonaktifkan.",
        "Mencegah Salah Klik: Guru di luar meja piket tidak akan secara tidak sengaja meng-ACC siswa.",
        "Monitoring Real-Time: Tetap bisa memantau siswa kelas binaannya yang sedang berada di luar sekolah.",
        "Akses Tukar Jadwal: Tersedia link cepat untuk mengajukan penggantian jadwal piket jika berhalangan."
    ]
    for pt in pts_pantau:
        p = tf.add_paragraph()
        p.text = "• " + pt
        p.font.size = Pt(12)
        p.font.color.rgb = C_TEXT_DARK
        p.space_after = Pt(8)

    # =========================================================================
    # SLIDE 7: FITUR GURU PIKET (2) - CETAK BLUETOOTH & DISPEN MANDIRI
    # =========================================================================
    slide7 = prs.slides.add_slide(blank_layout)
    add_header(slide7, "Fitur Guru Piket: Struk Thermal Bluetooth 58mm & Manual", "MODUL GURU PIKET")

    card_data7 = [
        ("🖨️ Cetak Bluetooth ESC/POS Direct", C_BLUE, [
            "Mendukung mini printer kasir 58mm portable.",
            "Teknologi Web Bluetooth API langsung dari Chrome/Edge.",
            "Tanpa Perlu Instal Driver: Hubungkan Bluetooth, klik Cetak, struk langsung keluar dalam 2 detik.",
            "Solusi Ideal bagi Siswa Tanpa HP / Baterai Habis."
        ]),
        ("📄 Unduh Struk Resmi PDF (58mm)", RGBColor(14, 165, 233), [
            "Template struk berformat pas untuk kertas kasir 58mm.",
            "Memuat Nomor Surat Resmi, Nama, Kelas, Alasan, QR Token, dan Tanda Tangan Guru Piket.",
            "Bisa dicetak menggunakan printer kabel meja piket.",
            "Bisa disimpan sebagai arsip digital sekolah."
        ]),
        ("⚡ Dispensasi Mandiri oleh Guru", C_GREEN, [
            "Khusus kasus darurat atau siswa dipanggil dinas mendadak oleh guru.",
            "Guru mencari nama siswa via Select2 AJAX pencarian cepat.",
            "Status Otomatis 'Disetujui': Tidak perlu antre approval.",
            "Langsung mencetak struk / terbit QR untuk satpam."
        ])
    ]

    for idx, (title, col, items) in enumerate(card_data7):
        l = Inches(0.8) + idx * Inches(3.95)
        w = Inches(3.75)
        t = Inches(1.8)
        h = Inches(5.0)

        add_card(slide7, l, t, w, h, C_WHITE, col)
        tb = slide7.shapes.add_textbox(l + Inches(0.2), t + Inches(0.25), w - Inches(0.4), h - Inches(0.5))
        tf = tb.text_frame
        tf.word_wrap = True

        p = tf.paragraphs[0]
        p.text = title
        p.font.size = Pt(14)
        p.font.bold = True
        p.font.color.rgb = col
        p.space_after = Pt(12)

        for item in items:
            p = tf.add_paragraph()
            p.text = "• " + item
            p.font.size = Pt(11)
            p.font.color.rgb = C_TEXT_DARK
            p.space_after = Pt(8)

    # =========================================================================
    # SLIDE 8: FITUR GURU PIKET (3) - CHECKLOG DINAS & TUKAR JADWAL
    # =========================================================================
    slide8 = prs.slides.add_slide(blank_layout)
    add_header(slide8, "Fitur Guru Piket: Checklog Dinas Luar & Tukar Jadwal", "MODUL GURU PIKET")

    # Card Kiri: Checklog Dinas Luar
    add_card(slide8, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), C_WHITE, C_BLUE)
    tb = slide8.shapes.add_textbox(Inches(1.1), Inches(2.0), Inches(5.0), Inches(4.6))
    tf = tb.text_frame
    tf.word_wrap = True

    p = tf.paragraphs[0]
    p.text = "📋 GURU CHECKLOG (DINAS LUAR GURU)"
    p.font.size = Pt(14)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p.space_after = Pt(10)

    p = tf.add_paragraph()
    p.text = "Mencatat mobilitas guru piket yang harus meninggalkan sekolah untuk tugas dinas:"
    p.font.size = Pt(11)
    p.font.color.rgb = C_TEXT_MUTED
    p.space_after = Pt(8)

    pts_checklog = [
        "Check-Out Dinas: Guru mengisi keperluan tugas dinas & perkiraan jam kembali.",
        "Status Transparan: Dashboard guru menampilkan status 'Sedang Keluar Dinas' agar rekan piket tahu.",
        "Konfirmasi Kembali: Begitu tiba di sekolah, klik 'Konfirmasi Tiba' agar status kembali aktif (standby).",
        "Jejak Audit Tertata: Membantu kepala sekolah & waka kurikulum merekap kehadiran dinas luar."
    ]
    for pt in pts_checklog:
        p = tf.add_paragraph()
        p.text = "✔ " + pt
        p.font.size = Pt(11)
        p.font.color.rgb = C_TEXT_DARK
        p.space_after = Pt(6)

    # Card Kanan: Tukar Jadwal Piket
    add_card(slide8, Inches(6.8), Inches(1.8), Inches(5.7), Inches(5.0), C_WHITE, C_GREEN)
    tb = slide8.shapes.add_textbox(Inches(7.1), Inches(2.0), Inches(5.1), Inches(4.6))
    tf = tb.text_frame
    tf.word_wrap = True

    p = tf.paragraphs[0]
    p.text = "🔄 TUKAR JADWAL PIKET (SHIFT SWAP)"
    p.font.size = Pt(14)
    p.font.bold = True
    p.font.color.rgb = C_GREEN
    p.space_after = Pt(10)

    p = tf.add_paragraph()
    p.text = "Solusi resmi saat guru berhalangan bertugas pada hari piket tertentu:"
    p.font.size = Pt(11)
    p.font.color.rgb = C_TEXT_MUTED
    p.space_after = Pt(8)

    pts_swap = [
        "Pencarian Guru Pengganti: Cari nama/NIP rekan guru yang bersedia menggantikan.",
        "Proteksi Tanggal Real-Time: Terkunci di bulan berjalan, tidak bisa backdate ke hari kemarin.",
        "Notifikasi Persetujuan: Guru pengganti menerima permintaan swap di dashboard & notif WhatsApp.",
        "Pemindahan Hak Otomatis: Saat disetujui, wewenang piket aktif resmi berpindah ke guru pengganti."
    ]
    for pt in pts_swap:
        p = tf.add_paragraph()
        p.text = "✔ " + pt
        p.font.size = Pt(11)
        p.font.color.rgb = C_TEXT_DARK
        p.space_after = Pt(6)

    # =========================================================================
    # SLIDE 9: FITUR SATPAM (1) - LIVE SCANNER & GATE PHOTO VERIFICATION
    # =========================================================================
    slide9 = prs.slides.add_slide(blank_layout)
    add_header(slide9, "Fitur Satpam: Scanner Cepat & Foto Bukti Pos Gerbang", "MODUL POS SATPAM")

    card_data9 = [
        ("📷 Live Camera Barcode/QR Scanner", C_RED, [
            "Mendukung kamera HP Android, Tablet, atau Laptop pos satpam.",
            "Tersedia tombol ganti kamera (Depan / Belakang) & senter flash untuk malam/mendung.",
            "Deteksi QR Token instan dalam sepersekian detik.",
            "Dilengkapi Audio Beep konfirmasi otomatis saat pembacaan sukses."
        ]),
        ("📸 Gate Photo Verification (Foto Gerbang)", C_NAVY_DARK, [
            "Wajib mengambil foto siswa di depan pos gerbang saat hendak keluar.",
            "Merekam kondisi fisik otentik: apakah siswa berseragam rapi dan membawa helm/kendaraan.",
            "Tersimpan permanen di server sebagai bukti fisik tak terbantahkan jika ada komplain orang tua.",
            "Mencegah praktik izin titipan atau joki gerbang."
        ]),
        ("📋 Dashboard Guru Piket Hari Ini", C_BLUE, [
            "Menampilkan daftar nama guru piket yang sedang standby di meja piket sesi aktif.",
            "Tersedia tombol Direct Call dan WhatsApp langsung ke meja piket.",
            "Memudahkan satpam mengonfirmasi izin mencurigakan tanpa harus meninggalkan pos gerbang."
        ])
    ]

    for idx, (title, col, items) in enumerate(card_data9):
        l = Inches(0.8) + idx * Inches(3.95)
        w = Inches(3.75)
        t = Inches(1.8)
        h = Inches(5.0)

        add_card(slide9, l, t, w, h, C_WHITE, col)
        tb = slide9.shapes.add_textbox(l + Inches(0.2), t + Inches(0.25), w - Inches(0.4), h - Inches(0.5))
        tf = tb.text_frame
        tf.word_wrap = True

        p = tf.paragraphs[0]
        p.text = title
        p.font.size = Pt(13)
        p.font.bold = True
        p.font.color.rgb = col
        p.space_after = Pt(12)

        for item in items:
            p = tf.add_paragraph()
            p.text = "• " + item
            p.font.size = Pt(11)
            p.font.color.rgb = C_TEXT_DARK
            p.space_after = Pt(8)

    # =========================================================================
    # SLIDE 10: FITUR SATPAM (2) - AUTO-OVERDUE & PENCARIAN MANUAL
    # =========================================================================
    slide10 = prs.slides.add_slide(blank_layout)
    add_header(slide10, "Fitur Satpam: Deteksi Terlambat & Pencarian Cadangan", "MODUL POS SATPAM")

    # Card Kiri: Auto Overdue
    add_card(slide10, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), C_WHITE, C_RED)
    tb = slide10.shapes.add_textbox(Inches(1.1), Inches(2.0), Inches(5.0), Inches(4.6))
    tf = tb.text_frame
    tf.word_wrap = True

    p = tf.paragraphs[0]
    p.text = "⏱️ AUTO-OVERDUE DETECTOR (SISWA TELAT)"
    p.font.size = Pt(14)
    p.font.bold = True
    p.font.color.rgb = C_RED
    p.space_after = Pt(10)

    p = tf.add_paragraph()
    p.text = "Evaluasi waktu kembali otomatis saat siswa discan masuk:"
    p.font.size = Pt(11)
    p.font.color.rgb = C_TEXT_MUTED
    p.space_after = Pt(8)

    pts_overdue = [
        "Layar Hijau (Tepat Waktu): Siswa kembali sebelum batas jam. Klik 'Konfirmasi Masuk', status selesai.",
        "Layar Merah (Terlambat/Overdue): Siswa telat ➔ Sistem menghitung persis berapa menit keterlambatan (misal: 'Telat 23 Menit').",
        "Penandaan Khusus (is_warned): Siswa otomatis ditandai terlambat dalam database.",
        "Alert Otomatis ke Guru Piket: Notifikasi WhatsApp langsung terkirim ke meja piket agar siswa dipanggil."
    ]
    for pt in pts_overdue:
        p = tf.add_paragraph()
        p.text = "✔ " + pt
        p.font.size = Pt(11)
        p.font.color.rgb = C_TEXT_DARK
        p.space_after = Pt(6)

    # Card Kanan: Pencarian Manual
    add_card(slide10, Inches(6.8), Inches(1.8), Inches(5.7), Inches(5.0), C_WHITE, C_BLUE)
    tb = slide10.shapes.add_textbox(Inches(7.1), Inches(2.0), Inches(5.1), Inches(4.6))
    tf = tb.text_frame
    tf.word_wrap = True

    p = tf.paragraphs[0]
    p.text = "🔍 TAB PENCARIAN & VERIFIKASI MANUAL"
    p.font.size = Pt(14)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p.space_after = Pt(10)

    p = tf.add_paragraph()
    p.text = "Cadangan darurat jika scan kamera mengalami kendala:"
    p.font.size = Pt(11)
    p.font.color.rgb = C_TEXT_MUTED
    p.space_after = Pt(8)

    pts_manual = [
        "Cari Berdasarkan NIS / Nama / Nomor Surat: Ketik 3 huruf, data izin langsung muncul.",
        "Solusi Layar HP Siswa Rusak: Jika layar siswa pecah/gelap, satpam cari manual.",
        "Solusi Kamera Pos Bermasalah: Tidak perlu menghambat gerbang, konfirmasi tetap bisa dilakukan.",
        "Monitoring Tab 'Siswa Sedang Keluar': Satpam bisa melihat daftar siapa saja yang belum kembali secara real-time."
    ]
    for pt in pts_manual:
        p = tf.add_paragraph()
        p.text = "✔ " + pt
        p.font.size = Pt(11)
        p.font.color.rgb = C_TEXT_DARK
        p.space_after = Pt(6)

    # =========================================================================
    # SLIDE 11: FITUR OTOMASI SISTEM & LAPORAN
    # =========================================================================
    slide11 = prs.slides.add_slide(blank_layout)
    add_header(slide11, "Fitur Otomasi Cerdas & Rekapitulasi Laporan", "OTOMASI & ADMINISTRASI")

    card_data11 = [
        ("🤖 Robot Pembersih Otomatis (Cron)", C_NAVY_DARK, [
            "Auto-Kadaluarsa: Pengajuan gantung yang tidak sempat di-ACC sampai jam sekolah usai otomatis hangus.",
            "Auto-Closed (Tidak Kembali): Siswa yang scan keluar tapi tidak scan masuk sampai sore otomatis ditandai 'Selesai (Tidak Kembali)'.",
            "Menjaga antrean sistem selalu bersih setiap pagi."
        ]),
        ("📊 Ekspor Laporan Excel (.xlsx) & PDF", C_GREEN, [
            "Filter komprehensif: berdasarkan tanggal, kelas, jurusan, atau jenis izin.",
            "Download Excel untuk rekapitulasi data Tata Usaha (TU).",
            "Download PDF resmi siap cetak untuk laporan mingguan Kepala Sekolah & bahan penanganan Guru BK."
        ]),
        ("🛡️ Keamanan & Integrasi SiPintu SSO", C_BLUE, [
            "Single Sign-On (SSO): Login menggunakan akun portal terpadu SiPintu SMKN 1 Bangsri.",
            "Audit Log Lengkap: Merekam jam login, IP address, waktu ACC guru, dan satpam petugas gerbang.",
            "Proteksi Anti-Brute Force: Akun otomatis terkunci 15 menit jika 5x gagal login."
        ])
    ]

    for idx, (title, col, items) in enumerate(card_data11):
        l = Inches(0.8) + idx * Inches(3.95)
        w = Inches(3.75)
        t = Inches(1.8)
        h = Inches(5.0)

        add_card(slide11, l, t, w, h, C_WHITE, col)
        tb = slide11.shapes.add_textbox(l + Inches(0.2), t + Inches(0.25), w - Inches(0.4), h - Inches(0.5))
        tf = tb.text_frame
        tf.word_wrap = True

        p = tf.paragraphs[0]
        p.text = title
        p.font.size = Pt(13)
        p.font.bold = True
        p.font.color.rgb = col
        p.space_after = Pt(12)

        for item in items:
            p = tf.add_paragraph()
            p.text = "• " + item
            p.font.size = Pt(11)
            p.font.color.rgb = C_TEXT_DARK
            p.space_after = Pt(8)

    # =========================================================================
    # SLIDE 12: TROUBLESHOOTING & PENANGANAN LAPANGAN
    # =========================================================================
    slide12 = prs.slides.add_slide(blank_layout)
    add_header(slide12, "Penanganan Cepat Kasus Lapangan (Troubleshooting)", "PANDUAN OPERASIONAL")

    # Table format
    rows = [
        ("Kendala di Sekolah", "Tindakan Guru Piket", "Tindakan Petugas Satpam"),
        ("Siswa tidak membawa HP / Baterai habis", "Klik tombol 'Cetak Bluetooth' atau 'Download PDF' struk 58mm.", "Pindai QR Code dari lembar kertas struk thermal siswa."),
        ("Kamera scanner HP Satpam error / gelap", "Pastikan guru piket sudah menyetujui izin siswa di sistem.", "Buka tab 'Pencarian Manual' ➔ Ketik NIS atau Nama siswa."),
        ("Wajah siswa beda dengan foto profil (Joki)", "Tolak izin & catat pelanggaran pada formulir penolakan.", "Tahan siswa di pos gerbang & hubungi guru piket melalui nomor di dashboard."),
        ("Siswa belum kembali melewati batas waktu", "Lihat tab 'Siswa Terlambat' di Dashboard & hubungi wali murid via WA.", "Saat siswa tiba, klik 'Konfirmasi Masuk (Overdue)' agar menit telat tercatat."),
        ("Guru piket berhalangan hadir", "Ajukan 'Tukar Jadwal Piket' ke guru pengganti sebelum jam sesi dimulai.", "Cek kartu 'Guru Piket Hari Ini' untuk melihat siapa petugas pengganti.")
    ]

    table_shape = slide12.shapes.add_table(len(rows), 3, Inches(0.8), Inches(1.8), Inches(11.7), Inches(5.0))
    table = table_shape.table
    table.columns[0].width = Inches(3.2)
    table.columns[1].width = Inches(4.25)
    table.columns[2].width = Inches(4.25)

    for r_idx, row in enumerate(rows):
        for c_idx, cell_text in enumerate(row):
            cell = table.cell(r_idx, c_idx)
            cell.text = cell_text
            cell.margin_left = cell.margin_right = cell.margin_top = cell.margin_bottom = Inches(0.1)
            p = cell.text_frame.paragraphs[0]
            if r_idx == 0:
                cell.fill.solid()
                cell.fill.fore_color.rgb = C_NAVY_DARK
                p.font.bold = True
                p.font.size = Pt(11)
                p.font.color.rgb = C_WHITE
            else:
                cell.fill.solid()
                cell.fill.fore_color.rgb = C_WHITE if r_idx % 2 == 1 else C_BG_LIGHT
                p.font.size = Pt(10)
                p.font.color.rgb = C_TEXT_DARK

    # =========================================================================
    # SLIDE 13: CHECKLIST TANGGUNG JAWAB HARIAN & PENUTUP
    # =========================================================================
    slide13 = prs.slides.add_slide(blank_layout)
    add_header(slide13, "Checklist Tanggung Jawab Harian & Penutup", "KOMITMEN BERSAMA")

    # Card Kiri: Checklist Guru Piket
    add_card(slide13, Inches(0.8), Inches(1.8), Inches(5.6), Inches(4.8), C_WHITE, C_BLUE)
    tb = slide13.shapes.add_textbox(Inches(1.1), Inches(2.0), Inches(5.0), Inches(4.4))
    tf = tb.text_frame
    tf.word_wrap = True

    p = tf.paragraphs[0]
    p.text = "📋 CHECKLIST HARIAN GURU PIKET"
    p.font.size = Pt(14)
    p.font.bold = True
    p.font.color.rgb = C_BLUE
    p.space_after = Pt(10)

    piket_checks = [
        "Login tepat waktu & pastikan muncul badge 'Sedang Bertugas Aktif'.",
        "Periksa antrean pengajuan 'Menunggu' secara berkala di ruang piket.",
        "Validasi bukti surat & kesesuaian jam kembali sebelum klik Setuju.",
        "Wajib berikan catatan alasan jika terpaksa menolak izin siswa.",
        "Cetak struk Bluetooth 58mm bagi siswa yang tidak membawa HP.",
        "Lakukan operan antrean tertunda jika berganti ke guru piket sesi berikutnya."
    ]
    for chk in piket_checks:
        p = tf.add_paragraph()
        p.text = "☑ " + chk
        p.font.size = Pt(11)
        p.font.color.rgb = C_TEXT_DARK
        p.space_after = Pt(6)

    # Card Kanan: Checklist Satpam
    add_card(slide13, Inches(6.8), Inches(1.8), Inches(5.7), Inches(4.8), C_WHITE, C_GREEN)
    tb = slide13.shapes.add_textbox(Inches(7.1), Inches(2.0), Inches(5.1), Inches(4.4))
    tf = tb.text_frame
    tf.word_wrap = True

    p = tf.paragraphs[0]
    p.text = "🛡️ CHECKLIST HARIAN SATPAM GERBANG"
    p.font.size = Pt(14)
    p.font.bold = True
    p.font.color.rgb = C_GREEN
    p.space_after = Pt(10)

    satpam_checks = [
        "Pastikan HP / Tablet pos dalam kondisi baterai terisi & kamera aktif.",
        "Jangan izinkan siswa keluar sebelum QR Code berhasil dipindai.",
        "Wajib ambil foto fisik siswa di depan pos gerbang saat siswa keluar.",
        "Pindai ulang QR Code saat siswa kembali untuk menutup izin.",
        "Perhatikan warna layar: jika Merah (Overdue), ingatkan siswa terkait keterlambatan.",
        "Gunakan pencarian manual jika scanner atau HP siswa mengalami kendala."
    ]
    for chk in satpam_checks:
        p = tf.add_paragraph()
        p.text = "☑ " + chk
        p.font.size = Pt(11)
        p.font.color.rgb = C_TEXT_DARK
        p.space_after = Pt(6)

    # Save PPTX
    output_pptx = "/home/zainal/Documents/Digipen/DIDISPEN_Sosialisasi_Alur_dan_Fitur.pptx"
    prs.save(output_pptx)
    print(f"Presentation saved successfully to: {output_pptx}")

if __name__ == "__main__":
    create_deck()
