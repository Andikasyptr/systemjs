<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk - @yield('title')</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 4mm;
            width: 50mm;
            line-height: 1.2;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .flex { display: flex; justify-content: space-between; }
        .border-b { border-bottom: 1px dashed #000; padding-bottom: 4px; margin-bottom: 4px; }
        .pt-2 { padding-top: 4px; }
        .space-y-1 > * + * { margin-top: 2px; }
    </style>
</head>
<body onload="window.print(); setTimeout(() => window.close(), 500);">
    @yield('content')
</body>
</html>