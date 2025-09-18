<html>
<head>
<style>
body {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

.watermark {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg); /* ← esto es diagonal ascendente */
    font-size: 120px;
    font-weight: bold;
    color: red;
    opacity: 0.1;
    white-space: nowrap;
    z-index: 999;
    pointer-events: none;
}
</style>
</head>
<body>

<div class="watermark">ANULADO</div>
</body>
</html>
