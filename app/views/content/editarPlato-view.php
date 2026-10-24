<?php
    use app\controllers\adminController;

    $rolActual = strtolower(trim($_SESSION['rol'] ?? ''));
    if (!isset($_SESSION['id_usuario']) || ($rolActual !== 'admin' && $rolActual !== 'administrador')) {
        header("Location: " . APP_URL . "login");
        exit();
    }

    $insAdmin = new adminController();
    $categorias = $insAdmin->obtenerCategorias();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modulo_editar_plato'])) {
        if (method_exists($insAdmin, 'actualizarPlato')) {
            $insAdmin->actualizarPlato();
        } else {
            $insAdmin->actualizarPlato();
        }
    }

    $id = 0;
    if (isset($url[1]) && is_numeric($url[1])) {
        $id = (int)$url[1];
    } elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id'];
    }

    $plato = ($id > 0) ? $insAdmin->obtenerProductoPorId($id) : null;

    if (!$plato) {
        header("Location: " . APP_URL . "carta");
        exit();
    }

    $imgSrc = APP_URL . "app/img/default.png";
    if (!empty($plato['img_url'])) {
        $archivoImg = $plato['img_url'];
        if (strpos($archivoImg, 'http') === 0) {
            $imgSrc = $archivoImg;
        } elseif (is_file("./app/views/img/productos/" . $archivoImg)) {
            $imgSrc = APP_URL . "app/views/img/productos/" . $archivoImg;
        } elseif (is_file("./app/img/productos/" . $archivoImg)) {
            $imgSrc = APP_URL . "app/img/productos/" . $archivoImg;
        } else {
            $imgSrc = APP_URL . "app/views/img/productos/" . $archivoImg;
        }
    }
?>

<main class="flex-1 p-6 md:p-8 max-w-3xl mx-auto w-full font-mono select-none">
  <div class="flex items-center justify-between mb-6">
    <a href="<?php echo APP_URL; ?>carta" class="px-5 py-2 rounded-full font-bold text-xs uppercase bg-[#182338] border border-[#26354D] text-slate-300 hover:text-white transition">
      Volver
    </a>
    <h2 class="text-base font-black text-white">Editar Producto #<?= $plato['id'] ?></h2>
  </div>

  <div class="bg-[#121A2A] border border-[#23324A] rounded-3xl p-8 shadow-2xl">
    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="modulo_editar_plato" value="1">
      <input type="hidden" name="id" value="<?= $plato['id'] ?>">
      <input type="hidden" name="imagen_actual" value="<?= htmlspecialchars($plato['img_url'] ?? '') ?>">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Nombre</label>
          <input type="text" name="nombre" required value="<?= htmlspecialchars($plato['nombre']) ?>" class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Categoría</label>
          <select name="categoria_id" required class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
            <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $plato['categoria_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Precio de Venta</label>
          <input type="number" step="100" name="precio_venta" required value="<?= (float)$plato['precio_venta'] ?>" class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Estado</label>
          <select name="estado" class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B]">
            <option value="Activo" <?= ($plato['estado'] === 'Activo') ? 'selected' : '' ?>>Activo</option>
            <option value="Inactivo" <?= ($plato['estado'] === 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Descripción</label>
        <textarea name="descripcion" rows="3" class="w-full bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B] font-sans"><?= htmlspecialchars($plato['descripcion'] ?? '') ?></textarea>
      </div>

      <div class="pt-2">
        <label class="block text-xs font-bold text-slate-300 mb-2 uppercase">Foto del Producto</label>
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 rounded-xl overflow-hidden bg-[#182338] border border-[#2E3F5C] shrink-0 flex items-center justify-center">
            <img id="img-preview" 
                 src="<?= $imgSrc ?>" 
                 onerror="this.onerror=null; this.src='<?php echo APP_URL; ?>app/img/default.png';" 
                 class="w-full h-full object-cover" 
                 alt="Vista previa">
          </div>
          <input type="file" id="input-imagen" name="imagen" accept="image/png, image/jpeg, image/webp" 
                 class="flex-1 bg-[#182338] border border-[#2E3F5C] rounded-xl px-4 py-2 text-slate-400 text-xs file:mr-4 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#F59E0B] file:text-[#0D1322] cursor-pointer">
        </div>
      </div>

      <div class="pt-4">
        <button type="submit" class="w-full py-3.5 rounded-full bg-[#F59E0B] text-[#0D1322] font-mono text-xs font-extrabold uppercase hover:brightness-110 active:scale-95 transition shadow-lg shadow-[#F59E0B]/20 cursor-pointer">
          Actualizar Producto
        </button>
      </div>
    </form>
  </div>
</main>