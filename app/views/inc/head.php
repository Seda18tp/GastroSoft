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
    body {
        background-color: #0D1322;
    }
</style>