<?php
    use app\controllers\adminController;

    $rolActual = strtolower(trim($_SESSION['rol'] ?? ''));
    if (!isset($_SESSION['id_usuario']) || ($rolActual !== 'admin' && $rolActual !== 'administrador')) {
        header("Location: " . APP_URL . "login");
        exit();
    }

    $insAdmin = new adminController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_cambiar_estado_prod'])) {
        $insAdmin->alternarEstadoProducto();
    }

    $catSeleccionada = 0;
    if (isset($_GET['cat']) && is_numeric($_GET['cat'])) {
        $catSeleccionada = (int)$_GET['cat'];
    } elseif (isset($url[1]) && is_numeric($url[1])) {
        $catSeleccionada = (int)$url[1];
    }

    $categorias = $insAdmin->obtenerCategorias();
    $productos = $insAdmin->obtenerProductosCarta($catSeleccionada);
?>

<main class="flex-1 p-6 md:p-8 max-w-7xl mx-auto w-full font-sans select-none">
  
  <!--nav-->
  <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div class="flex flex-wrap items-center gap-2">
      <a href="<?php echo APP_URL; ?>admin" class="px-5 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
        Volver
      </a>
      <a href="<?php echo APP_URL; ?>estadisticas" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
        Estadisticas
      </a>
      <a href="<?php echo APP_URL; ?>carta" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#F59E0B] text-[#0D1322] shadow-md shadow-[#F59E0B]/20 transition">
        Carta
      </a>
      <a href="<?php echo APP_URL; ?>inventario" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
        Inventario
      </a>
      <a href="<?php echo APP_URL; ?>usuarios" class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider bg-[#162032] border border-[#26354D] text-[#8A99AD] hover:text-white transition">
        Usuarios
      </a>
    </div>

    <a href="<?php echo APP_URL; ?>nuevoPlato" class="px-5 py-2 rounded-full font-mono text-xs font-bold uppercase tracking-wider bg-[#10B981] text-[#0D1322] hover:brightness-110 shadow-lg shadow-[#10B981]/20 transition">
      + Nuevo Plato
    </a>
  </div>

  <!--FiltroCategorias-->
  <div class="flex items-center gap-2 mb-8 overflow-x-auto pb-2 font-mono text-xs">
    <button type="button" 
            onclick="filtrarPlatos(0, this)" 
            class="filtro-btn px-4 py-1.5 rounded-full font-bold transition <?= ($catSeleccionada === 0) ? 'bg-[#F59E0B] text-[#0D1322]' : 'bg-[#182338] border border-[#26354D] text-slate-300 hover:text-white' ?>">
      Todos
    </button>

    <?php foreach ($categorias as $cat): ?>
      <button type="button" 
              onclick="filtrarPlatos(<?= $cat['id'] ?>, this)" 
              class="filtro-btn px-4 py-1.5 rounded-full font-bold transition <?= ($catSeleccionada === (int)$cat['id']) ? 'bg-[#F59E0B] text-[#0D1322]' : 'bg-[#182338] border border-[#26354D] text-slate-300 hover:text-white' ?>">
        <?= htmlspecialchars($cat['nombre']) ?>
      </button>
    <?php endforeach; ?>
  </div>

  <!--Tarjetas-->
  <div id="grid-productos" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php if (empty($productos)): ?>
      <div id="sin-productos" class="col-span-full py-16 text-center text-slate-500 font-mono text-xs">
        No se encontraron productos en esta categoría.
      </div>
    <?php else: ?>
    <?php foreach ($productos as $prod): 
        $activo = (strtolower(trim($prod['estado'] ?? '')) === 'activo');
        $imgBd  = trim($prod['img_url'] ?? '');

        if (str_starts_with($imgBd, 'data:image/')) {
            $foto = $imgBd;
        } elseif (str_starts_with($imgBd, 'http://') || str_starts_with($imgBd, 'https://')) {
            $foto = $imgBd;
        } elseif (!empty($imgBd) && file_exists(dirname(__DIR__, 2) . '/img/productos/' . $imgBd)) {
            $foto = APP_URL . "app/img/productos/" . htmlspecialchars($imgBd);
        } else {
            $foto = APP_URL . "app/img/default.png";
        }
      ?>
      
  <div class="tarjeta-producto bg-[#121929] border border-[#23324A] rounded-2xl p-5 shadow-xl flex flex-col justify-between hover:border-[#2E3F5C] transition-all"
       data-categoria="<?= $prod['categoria_id'] ?>">
    
    <div>
      <div class="flex items-start gap-4">
        <div class="w-14 h-14 rounded-xl overflow-hidden bg-[#182338] border border-[#2E3F5C] shrink-0 flex items-center justify-center">
          <img src="<?= $foto ?>" class="w-full h-full object-cover" alt="Foto">
        </div>

        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-2">
            <h3 class="font-bold text-white text-sm leading-tight truncate">
              <?= htmlspecialchars($prod['nombre']) ?>
            </h3>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold shrink-0 <?= $activo ? 'bg-[#10B981]/15 text-[#10B981] border border-[#10B981]/30' : 'bg-rose-500/15 text-rose-400 border border-rose-500/30' ?>">
              <?= $activo ? 'Activo' : 'Inactivo' ?>
            </span>
          </div>

          <p class="text-[11px] text-slate-400 mt-1.5 font-sans line-clamp-2 leading-relaxed">
            <?= !empty($prod['descripcion']) ? htmlspecialchars($prod['descripcion']) : 'Sin descripción.' ?>
          </p>
        </div>
      </div>
    </div>

    <div class="flex items-center justify-between mt-5 pt-4 border-t border-[#23324A]">
      <span class="font-mono text-base font-extrabold text-[#F59E0B]">
        $ <?= number_format($prod['precio_venta'], 0, ',', '.') ?>
      </span>

      <div class="flex items-center gap-4 font-mono text-xs">
        <a href="<?php echo APP_URL; ?>editarPlato/<?= $prod['id'] ?>?id=<?= $prod['id'] ?>" 
           class="text-slate-300 hover:text-white font-semibold transition">
          Editar
        </a>

        <form action="" method="POST" class="inline m-0 p-0">
          <input type="hidden" name="accion_cambiar_estado_prod" value="1">
          <input type="hidden" name="producto_id" value="<?= $prod['id'] ?>">
          <input type="hidden" name="estado_actual" value="<?= htmlspecialchars($prod['estado']) ?>">
          <button type="submit" class="font-semibold transition cursor-pointer <?= $activo ? 'text-rose-400 hover:underline' : 'text-emerald-400 hover:underline' ?>">
            <?= $activo ? 'Desactivar' : 'Activar' ?>
          </button>
        </form>
      </div>
    </div>

  </div>
<?php endforeach; ?>
    <?php endif; ?>
  </div>

</main>