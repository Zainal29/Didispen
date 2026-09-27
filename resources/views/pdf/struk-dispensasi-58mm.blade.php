
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Dispensasi - {{ $dispensasi->nomor_surat }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
        }

        body {
            width: 58mm;
            padding: 2mm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            line-height: 1.15;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }

        .logo {
            width: 34px;
            height: 34px;
            object-fit: contain;
            margin: 0 auto 3px;
            display: block;
            filter: grayscale(100%) contrast(1.2);
        }

        .school-name {
            font-weight: bold;
            font-size: 11px;
            margin: 2px 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .school-address {
            font-size: 8px;
            margin: 1px 0;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            margin: 6px 0;
            text-decoration: underline;
            letter-spacing: 0.7px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 1.5px 0;
            vertical-align: top;
        }

        .info-table td.label {
            width: 25%;
            white-space: nowrap;
        }

        .info-table td.separator {
            width: 5%;
            text-align: center;
        }

        .info-table td.value {
            width: 70%;
            word-wrap: break-word;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .signature-section {
            margin-top: 7px;
            text-align: right;
            padding-right: 3px;
        }

        .signature-section .date {
            font-size: 8px;
            margin: 0 0 2px;
        }

        .signature-section .role {
            font-size: 8px;
            margin: 0 0 25px;
        }

        .signature-section .name {
            font-weight: bold;
            font-size: 9px;
            border-top: 1px solid #000;
            display: inline-block;
            padding-top: 1px;
            min-width: 90px;
        }

        .signature-section .nip {
            font-size: 8px;
            margin: 1px 0 0;
        }

        .footer {
            text-align: center;
            font-size: 8px;
            margin-top: 7px;
        }

        .footer p {
            margin: 2px 0;
        }

        .footer-note {
            font-size: 8px;
            font-style: italic;
            margin-top: 2px !important;
        }

        .thank-you {
            margin-top: 6px !important;
            font-weight: bold;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        @php
            $logoPath = public_path('images/logo-didispen.png');
            $logoBase64 = null;

            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        @endphp

        @if($logoBase64)
            <img
                src="data:image/png;base64,{{ $logoBase64 }}"
                class="logo"
                alt="Logo"
            >
        @else
            <div style="margin-bottom: 3px;">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="28"
                    height="28"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="#000"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    style="display:inline-block;"
                >
                    <path d="M14 22v-4a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v4"/>
                    <path d="M18 10h4l-10-8-10 8h4v12h12v-12z"/>
                    <path d="M6 10v12"/>
                    <path d="M18 10v12"/>
                </svg>
            </div>
        @endif

        <p class="school-name">SMKN 1 BANGSRI</p>
        <!-- <p class="school-address">SMKN1 Bangsri, Kab. Jepara</p> -->
        <p class="school-address">Sistem Informasi Dispensasi</p>
    </div>


    {{-- JUDUL --}}
    <div class="title">
        BUKTI DISPENSASI
    </div>


    {{-- DATA --}}
    <table class="info-table">

        <tr>
            <td class="label">No. Surat</td>
            <td class="separator">:</td>
            <td class="value">
                {{ $dispensasi->nomor_surat }}
            </td>
        </tr>

        <tr>
            <td class="label">NIS</td>
            <td class="separator">:</td>
            <td class="value">
                {{ $dispensasi->siswa?->user?->nis_nip ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="label">Nama</td>
            <td class="separator">:</td>
            <td class="value">
                {{ $dispensasi->siswa->nama_lengkap }}
            </td>
        </tr>

        <tr>
            <td class="label">Kelas</td>
            <td class="separator">:</td>
            <td class="value">
                {{ $dispensasi->siswa->kelas->nama_kelas ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="label">Tujuan</td>
            <td class="separator">:</td>
            <td class="value">
                {{ $dispensasi->tujuan }}
            </td>
        </tr>

        <tr>
            <td class="label">Lokasi</td>
            <td class="separator">:</td>
            <td class="value">
                {{ $dispensasi->lokasi }}
            </td>
        </tr>

        {{-- JAM DIGABUNG --}}
        <tr>
            <td class="label">Jam</td>
            <td class="separator">:</td>
            <td class="value">
                {{ \Carbon\Carbon::parse($dispensasi->jam_keluar)->format('H.i') }}
                -
                {{ \Carbon\Carbon::parse($dispensasi->jam_kembali)->format('H.i') }}
            </td>
        </tr>

    </table>


    <div class="divider"></div>


    {{-- TANDA TANGAN --}}
    <div class="signature-section">

        <p class="date">
           Bangsri, {{ now()->format('d/m/Y') }}
        </p>

        <p class="role">
            Guru Piket,
        </p>

        <p class="name">
            {{ $dispensasi->guru?->nama_lengkap
                ?? $dispensasi->guru?->user?->name
                ?? 'Guru Piket' }}
        </p>

        @if(!empty(
            $dispensasi->guru?->nip
            ?? $dispensasi->guru?->user?->nis_nip
        ))
            <p class="nip">
                NIP. {{
                    $dispensasi->guru?->nip
                    ?? $dispensasi->guru?->user?->nis_nip
                }}
            </p>
        @endif

    </div>


    <div class="divider"></div>


    {{-- FOOTER --}}
    <div class="footer">

        <p>
            Dicetak: {{ now()->format('d/m/Y H:i') }} WIB
        </p>

        <p class="footer-note">
            Struk ini sah jika ditandatangani<br>
            oleh Guru Piket.
        </p>

        <p class="thank-you">
            - TERIMA KASIH -
        </p>

    </div>

</body>
</html>