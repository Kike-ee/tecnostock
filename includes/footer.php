</main>
<?php foreach (($scripts ?? []) as $js): ?>
<script src="<?= BASE_URL ?>/assets/js/<?= e($js) ?>"></script>
<?php endforeach; ?>
</body>
</html>
