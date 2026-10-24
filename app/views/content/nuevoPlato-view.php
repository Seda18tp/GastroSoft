<?php
    use app\controllers\adminController;

    $rolActual = strtolower(trim($_SESSION['rol'] ?? ''));
    if (!isset($_SESSION['id_usuario']) || ($rolActual !== 'admin' && $rolActual !== 'administrador')) {
        header("Location: " . APP_URL . "login");
        exit();
    }

    $insAdmin = new adminController();
    $categorias = $insAdmin->obtenerCategorias();
    $alerta = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modulo_nuevo_plato'])) {
        if (method_exists($insAdmin, 'registrarPlato')) {
            $alerta = $insAdmin->registrarPlato();
        } else {
            $alerta = $insAdmin->registrarPlato();
        }
    }
?>

<main class="flex-1 p-6 md:p-8 max-w-3xl mx-auto w-full font-mono select-none">
  <div class="flex items-center justify-between mb-6">
    <a href="<?php echo APP_URL; ?>carta" class="px-5 py-2 rounded-full font-bold text-xs uppercase bg-[#182338] border border-[#26354D] text-slate-300 hover:text-white transition">
      Volver
    </a>
    <h2 class="text-base font-black text-white">Nuevo Plato</h2>
  </div>

  <div class="bg-[#121A2A] border border-[#23324A] rounded-3xl p-8 shadow-2xl">
    <?php if (isset($alerta)): ?>
      <div class="mb-5 p-3.5 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs">
        <?= htmlspecialchars($alerta['texto']) ?>
      </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="modulo_nuevo_plato" value="1">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Nombre</label>
          <input type="text" name="nombre" required placeholder="Ej: Hamburguesa Gastro" class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Categoría</label>
          <select name="categoria_id" required class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
            <option value="">Seleccione categoría...</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Precio de Venta</label>
          <input type="number" step="100" name="precio_venta" required placeholder="Ej: 32000" class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Estado Inicial</label>
          <select name="estado" class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
            <option value="Activo">Activo</option>
            <option value="Inactivo">Inactivo</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Descripción</label>
        <textarea name="descripcion" rows="3" placeholder="Ingredientes o detalles..." class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B] font-sans"></textarea>
      </div>

      <div class="pt-2">
        <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Foto del Producto (Opcional)</label>
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 rounded-xl overflow-hidden bg-[#182338] border border-[#2E3F5C] shrink-0 flex items-center justify-center">
            <img id="img-preview-nuevo" src="<?php echo APP_URL; ?>app/img/default.png" class="w-full h-full object-cover" alt="Vista previa">
          </div>
          <input type="file" id="input-imagen-nuevo" name="imagen" accept="image/png, image/jpeg, image/webp" class="flex-1 bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2 text-slate-400 text-xs file:mr-4 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#F59E0B] file:text-[#0D1322] cursor-pointer">
        </div>
      </div>

      <div class="pt-4">
        <button type="submit" class="w-full py-3.5 rounded-full bg-[#10B981] text-[#0D1322] font-mono text-xs font-extrabold uppercase hover:brightness-110 active:scale-95 transition shadow-lg shadow-[#10B981]/20 cursor-pointer">
          Guardar Producto
        </button>
      </div>
    </form>
  </div>
</main>
