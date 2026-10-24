<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Public Sans", -apple-system, "Segoe UI", Roboto, Arial, sans-serif; font-size: 12px; color: #1f2937; background: #eef1f5; }
        .toolbar { position: sticky; top: 0; display: flex; gap: 8px; justify-content: center; align-items: center; padding: 10px; background: #1f2937; color: #fff; z-index: 10; }
        .toolbar button { border: 0; border-radius: 6px; padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .toolbar .primary { background: #0d6efd; color: #fff; }
        .toolbar .secondary { background: #e5e7eb; color: #1f2937; }
        .toolbar small { opacity: .75; margin-left: 8px; }
        .page { width: 210mm; min-height: 297mm; margin: 16px auto; padding: 14mm 12mm; background: #fff; box-shadow: 0 2px 12px rgba(0, 0, 0, .08); }
        .doc-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; border-bottom: 2px solid #1f2937; padding-bottom: 10px; margin-bottom: 14px; }
        .doc-header h1 { margin: 0 0 4px; font-size: 20px; }
        .doc-header .meta { text-align: right; }
        .muted { color: #6b7280; }
        .logo { max-height: 56px; max-width: 160px; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-size: 10.5px; text-transform: uppercase; letter-spacing: .03em; color: #4b5563; }
        .num { text-align: right; white-space: nowrap; }
        tfoot td { font-weight: 700; background: #f9fafb; border-top: 2px solid #1f2937; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 10px; font-size: 10px; font-weight: 600; background: #e5e7eb; }
        .badge.paid { background: #d1fae5; color: #065f46; }
        .badge.unpaid { background: #fee2e2; color: #991b1b; }
        .badge.partial { background: #fef3c7; color: #92400e; }
        .badge.canceled { background: #e5e7eb; color: #4b5563; text-decoration: line-through; }
        .text-danger { color: #b91c1c; }
        .text-success { color: #047857; }
        .summary { display: flex; gap: 10px; margin-bottom: 14px; }
        .summary div { flex: 1; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 10px; }
        .summary strong { display: block; font-size: 15px; margin-top: 2px; }
        .doc-footer { margin-top: 18px; padding-top: 8px; border-top: 1px dashed #9ca3af; display: flex; justify-content: space-between; font-size: 10px; color: #6b7280; }
        @page { size: A4; margin: 10mm; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .page { width: auto; min-height: auto; margin: 0; padding: 0; box-shadow: none; }
            tr { page-break-inside: avoid; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="primary" onclick="window.print()">Print / Save as PDF</button>
        <button type="button" class="secondary" onclick="window.close()">Close</button>
        <small>Choose "Save as PDF" in the print dialog to keep a file.</small>
    </div>

    <div class="page">
        @yield('content')

        <div class="doc-footer">
            <span>Printed {{ now()->format('d M Y H:i') }} by {{ auth()->user()?->name }}</span>
            <span>Powered by Tech Scales Co. Ltd</span>
        </div>
    </div>

    <script>
        // Open the print dialog straight away, like the receipt does
        window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });
    </script>
</body>
</html>
