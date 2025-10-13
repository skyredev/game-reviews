<?php
$title = 'Hlavní stránka';
ob_start();
?>
<section class="home-page">
    <div class="container">
        <h1>Vítejte na Game Reviews</h1>
        <p>Objevte recenze nejnovějších her, hodnocení uživatelů a nejnovější herní zprávy.</p>
        <div class="top-games-container">
            <h2>Nejlépe hodnocené hry</h2>
            </div>
        </div>
</section>
<?php
$content = ob_get_clean();
