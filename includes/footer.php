<?php $appLayout = $appLayout ?? false; ?>
<?php if ($appLayout): ?>
  </main>
</div>
<?php endif; ?>
<div id="toasts" class="toasts" aria-live="polite"></div>
<script src="<?= e(url('assets/js/app.js')) ?>"></script>
<?php foreach (($scripts ?? []) as $s): ?>
<script src="<?= e(url('assets/js/' . $s)) ?>"></script>
<?php endforeach; ?>
<script>
<?php foreach (pull_flash() as $f): ?>
  toast(<?= json_encode($f['msg']) ?>, <?= json_encode($f['type']) ?>);
<?php endforeach; ?>
</script>
</body>
</html>
