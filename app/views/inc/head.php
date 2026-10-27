<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo defined('APP_NAME') ? APP_NAME : 'GastroSoft'; ?></title>
<link rel="shortcut icon" href="<?php echo APP_URL; ?>app/img/favicon.ico" type="image/x-icon">

<!-- CDN Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    darkBg: '#0d131f',     
                    cardBg: '#1b2234',     
                    cardBorder: '#273149', 
                    brandAccent: '#f59e0b',
                    greenAccent: '#10B981'
                }
            }
        }
    }
</script>

<style>
    html, body {
        overflow-x: hidden;
        width: 100%;
        background-color: #0D1322;
        color: #F8FAFC;
    }

    /* Tablas adaptables con scroll horizontal táctil */
    .table-responsive-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* tarjetas responsivas */
    .grid-auto-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.75rem;
    }
    @media (min-width: 640px) {
        .grid-auto-cards {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
        }
    }

    /* Scrollbars */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: #0D1322;
    }
    ::-webkit-scrollbar-thumb {
        background: #23324A;
        border-radius: 9999px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #3B82F6;
    }
</style>