<?php
    use app\controllers\meseroController;

    $rolActual = strtolower(trim($_SESSION['rol'] ?? ''));
    if (!isset($_SESSION['id_usuario'])) {
        header("Location: " . APP_URL . "login");
        exit();
    }

    $insMesero = new meseroController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['modulo_agregar_plato'])) {
            $insMesero->agregarPlato();
        } elseif (isset($_POST['modulo_pedir_cuenta'])) {
            $insMesero->pedirCuenta();
        } elseif (isset($_POST['modulo_enviar_cocina'])) {
            $insMesero->enviarCocina();
        }
    }

    $mesas = $insMesero->listarMesas();
    $zonas = $insMesero->listarZonas();
    $notificaciones = $insMesero->listarNotificaciones();
    $productos = $insMesero->listarProductos();
    $mesaParamId = (int)($_GET['mesa'] ?? 0);
?>

<main class="flex-1 w-full max-w-[1450px] mx-auto p-4 sm:p-6 font-sans select-none flex flex-col lg:flex-row gap-6">
  
  <!-- Sección Izquierda: Mesas y Notificaciones -->
  <section class="flex-1 flex flex-col gap-6">

    <!-- Contenedor Principal Mesas -->
    <div class="bg-[#121A2A] border border-[#23324A] rounded-3xl p-6 shadow-2xl">
      
      <!-- Filtros Dinámicos (Sin recargar pantalla) -->
      <div class="flex items-center gap-3 mb-6">
        <button type="button" 
                onclick="filtrarMesasPorZona('')" 
                id="btn-filtro-todas"
                class="px-5 py-2 rounded-full font-mono text-xs font-bold uppercase tracking-wider bg-[#F59E0B] text-[#0D1322] shadow-md shadow-[#F59E0B]/20 transition cursor-pointer">
          Todas las mesas
        </button>

        <select id="select-zona" 
                onchange="filtrarMesasPorZona(this.value)" 
                class="px-4 py-2 rounded-full font-mono text-xs font-bold uppercase tracking-wider bg-[#182338] border border-[#2E3F5C] text-white hover:border-[#F59E0B] cursor-pointer focus:outline-none">
          <option value="">Filtrar Zona</option>
          <optgroup label="Zonas Físicas">
            <?php foreach ($zonas as $z): ?>
              <option value="zona:<?= htmlspecialchars($z['ubicacion']) ?>"><?= htmlspecialchars($z['ubicacion']) ?></option>
            <?php endforeach; ?>
          </optgroup>
          <optgroup label="Estados">
            <option value="estado:disponible">Disponible</option>
            <option value="estado:ocupada">Ocupada</option>
            <option value="estado:precuenta">Precuenta</option>
          </optgroup>
        </select>
      </div>

      <!-- Cuadrícula de Mesas -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4" id="contenedor-tarjetas-mesas">
        <?php foreach ($mesas as $m): 
            $estado = strtolower(trim($m['mesa_estado'] ?? 'disponible'));
            if ($estado === 'libre') $estado = 'disponible';

            if ($estado === 'precuenta') {
                $colorClase = 'bg-[#F59E0B] text-[#0D1322] border-[#F59E0B]';
                $labelEstado = 'PRECUENTA';
            } elseif ($estado === 'ocupada') {
                $colorClase = 'bg-[#121A2A] text-rose-400 border-rose-500/40';
                $labelEstado = 'OCUPADA';
            } else {
                $colorClase = 'bg-[#121A2A] text-[#10B981] border-[#23324A] hover:border-[#10B981]';
                $labelEstado = 'DISPONIBLE';
            }
        ?>
          <div onclick='seleccionarMesa(<?= json_encode($m, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, this)' 
               data-mesa-id="<?= $m['mesa_id'] ?>"
               data-zona="<?= htmlspecialchars(strtolower($m['ubicacion'])) ?>"
               data-estado="<?= $estado ?>"
               class="tarjeta-mesa h-32 rounded-2xl flex flex-col items-center justify-center p-3 font-mono transition transform active:scale-95 shadow-lg cursor-pointer border <?= $colorClase ?>">
            <span class="text-3xl font-extrabold leading-none"><?= htmlspecialchars($m['numero_mesa']) ?></span>
            <span class="text-xs font-bold mt-2 uppercase tracking-wider"><?= $labelEstado ?></span>
          </div>
        <?php endforeach; ?>
      </div>

    </div>

    <!-- Notificaciones Enviadas por Cocina -->
    <div class="bg-[#121A2A] border border-[#23324A] rounded-3xl p-6 shadow-xl">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-mono text-sm font-bold text-white tracking-wide">Notificaciones</h3>
        <span class="px-3 py-1 rounded-full font-mono text-[11px] font-bold bg-[#F59E0B] text-[#0D1322]">
          Cocina
        </span>
      </div>
      <div class="space-y-3">
        <?php if (!empty($notificaciones)): ?>
          <?php foreach ($notificaciones as $noti): ?>
            <div class="h-11 px-4 rounded-full bg-[#182338] border border-[#2E3F5C] flex items-center justify-between font-mono text-xs text-slate-300">
              <span class="truncate pr-3 font-sans"><?= htmlspecialchars($noti['mensaje']) ?></span>
              <span class="text-[11px] text-slate-400 font-mono shrink-0"><?= $noti['hora'] ?></span>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="h-11 rounded-full bg-[#182338] border border-[#2E3F5C] flex items-center px-4 font-mono text-xs text-slate-400">
            No hay notificaciones de cocina en este momento.
          </div>
        <?php endif; ?>
      </div>
    </div>

  </section>

  <!-- Panel Lateral Derecho: Mesa Seleccionada -->
  <aside class="w-full lg:w-[420px] bg-[#121A2A] border border-[#23324A] rounded-3xl p-6 shadow-2xl flex flex-col justify-between">
    
    <div>
      <!-- Cabecera -->
      <div class="flex items-center justify-between pb-4 border-b border-[#23324A]">
        <div>
          <h2 class="font-mono text-lg font-black text-white" id="panel-mesa-titulo">
            Mesa #--
          </h2>
          <span class="font-mono text-xs text-slate-400" id="panel-mesa-pedido">
            Sin pedido activo
          </span>
        </div>

        <span class="px-4 py-1 rounded-full font-mono text-xs font-bold border border-[#26354D] text-slate-400" id="panel-mesa-badge">
          --
        </span>
      </div>

      <!-- Ítems del Pedido -->
      <div class="mt-6 space-y-4 max-h-[360px] overflow-y-auto pr-1" id="panel-mesa-items">
        <p class="font-mono text-xs text-slate-500 py-16 text-center">Selecciona una mesa para gestionar su orden.</p>
      </div>
    </div>

    <!-- Botonera y Subtotal -->
    <div class="pt-6 border-t border-[#23324A] mt-8">
      
      <!-- Subtotal -->
      <div class="flex items-center justify-between mb-6 font-mono">
        <span class="text-xs text-slate-400 uppercase tracking-wider">Subtotal:</span>
        <span class="text-xl font-black text-white" id="panel-mesa-subtotal">
          $ 0
        </span>
      </div>

      <!-- Botones de Acción -->
      <div class="grid grid-cols-2 gap-3 mb-3">
        <!-- Formulario Pedir Cuenta -->
        <form action="" method="POST" class="m-0 p-0" id="form-pedir-cuenta">
          <input type="hidden" name="modulo_pedir_cuenta" value="1">
          <input type="hidden" name="mesa_id" id="accion-mesa-id-cuenta" value="">
          <button type="submit" 
                  id="btn-pedir-cuenta" 
                  disabled
                  class="w-full py-3 px-3 rounded-full font-mono text-xs font-bold text-center border border-[#F59E0B] text-[#F59E0B] bg-[#182338] hover:bg-[#F59E0B]/10 active:scale-95 transition disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer">
            Pedir cuenta
          </button>
        </form>

        <!-- Botón que abre el modal para agregar platos en la misma pantalla -->
        <button type="button" 
                id="btn-abrir-modal-plato" 
                onclick="abrirModalAgregarPlato()" 
                disabled
                class="py-3 px-3 rounded-full font-mono text-xs font-bold text-center bg-[#10B981] hover:brightness-110 text-[#0D1322] active:scale-95 transition shadow-md shadow-[#10B981]/20 disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center">
          + Añadir platos
        </button>
      </div>

      <!-- Formulario Enviar a Cocina -->
      <form action="" method="POST" class="m-0 p-0" id="form-enviar-cocina">
        <input type="hidden" name="modulo_enviar_cocina" value="1">
        <input type="hidden" name="mesa_id" id="accion-mesa-id-cocina" value="">
        <input type="hidden" name="pedido_id" id="accion-pedido-id-cocina" value="">
        <button type="submit" 
                id="btn-enviar-cocina" 
                disabled
                onclick="return confirm('¿Enviar esta comanda a cocina para preparación?')"
                class="w-full py-3.5 rounded-2xl font-mono text-xs font-extrabold text-center uppercase tracking-wider bg-[#F59E0B] text-[#0D1322] hover:brightness-110 active:scale-98 transition shadow-lg shadow-[#F59E0B]/20 disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer">
          Enviar pedido a cocina
        </button>
      </form>

    </div>

  </aside>

</main>

<!-- Modal Extendido con Fotos para Añadir Platos -->
<div id="modal-agregar-plato" class="fixed inset-0 bg-[#0A0F1D]/85 backdrop-blur-md z-50 hidden flex items-center justify-center p-3 sm:p-6 font-mono">
  <div class="bg-[#121A2A] border border-[#23324A] rounded-3xl p-6 max-w-5xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
    
    <!-- Encabezado y Buscador -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-[#23324A] gap-4">
      <div>
        <h3 class="text-white font-black text-base uppercase tracking-wide" id="modal-titulo-agregar">
          Añadir Producto a Mesa #--
        </h3>
        <p class="text-[11px] text-slate-400 font-sans mt-0.5">Selecciona el producto del catálogo visual</p>
      </div>

      <div class="flex items-center gap-3">
        <div class="relative w-full sm:w-64">
          <input type="text" 
                 id="buscador-producto-modal" 
                 oninput="filtrarCatalogoModal(this.value)"
                 placeholder="Buscar plato o bebida..." 
                 class="w-full bg-[#182338] border border-[#2E3F5C] rounded-full px-4 py-2 text-white text-xs placeholder-slate-500 focus:outline-none focus:border-[#F59E0B]">
        </div>
        <button type="button" onclick="cerrarModalAgregarPlato()" class="text-slate-400 hover:text-white text-xl px-2 cursor-pointer">✕</button>
      </div>
    </div>

    <!-- Formulario Principal del Modal -->
    <form action="" method="POST" autocomplete="off" class="flex-1 flex flex-col justify-between overflow-hidden mt-4">
      <input type="hidden" name="modulo_agregar_plato" value="1">
      <input type="hidden" name="mesa_id" id="modal-mesa-id-plato" value="">
      <input type="hidden" name="producto_id" id="modal-producto-id-seleccionado" required value="">

      <!-- Cuadrícula de Productos con Fotos (Scroll) -->
      <div class="flex-1 overflow-y-auto pr-1 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5 mb-4" id="catalogo-productos-grid">
  <?php foreach ($productos as $p): 
      $fotoProducto = trim($p['img_url'] ?? '');
      $rutaFisicaProd = dirname(__DIR__, 2) . '/img/productos/' . $fotoProducto;

      if (!empty($fotoProducto) && file_exists($rutaFisicaProd)) {
          $rutaImg = APP_URL . 'app/img/productos/' . $fotoProducto;
      } else {
          $rutaImg = APP_URL . 'app/img/default.png';
      }
  ?>
    <div onclick="seleccionarProductoCard(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nombre'])) ?>', <?= $p['precio_venta'] ?>, this)"
         data-nombre="<?= htmlspecialchars(strtolower($p['nombre'])) ?>"
         class="card-producto-item group bg-[#182338] border border-[#26354D] hover:border-[#10B981] rounded-2xl p-2.5 flex flex-col justify-between cursor-pointer transition transform hover:scale-[1.02]">
      
      <div class="w-full h-28 bg-[#121A2A] rounded-xl overflow-hidden mb-2.5 flex items-center justify-center border border-[#26354D]/50 relative">
        <img src="<?= $rutaImg ?>" 
             alt="<?= htmlspecialchars($p['nombre']) ?>" 
             class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
             onerror="this.onerror=null; this.src='<?= APP_URL ?>app/img/default.png';">
      </div>

      <div>
        <h4 class="font-bold text-xs text-white truncate font-sans"><?= htmlspecialchars($p['nombre']) ?></h4>
        <span class="text-xs font-black text-[#10B981] mt-1 block">
          $ <?= number_format($p['precio_venta'], 0, ',', '.') ?>
        </span>
      </div>
    </div>
  <?php endforeach; ?>
</div>

      <!-- Barra Inferior de Configuración (Cantidad, Notas y Confirmación) -->
      <div class="pt-4 border-t border-[#23324A] bg-[#121A2A] grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
        
        <!-- Producto Seleccionado -->
        <div class="md:col-span-4 bg-[#182338] px-3.5 py-2.5 rounded-xl border border-[#26354D]">
          <span class="text-[10px] text-slate-400 block uppercase font-bold">Seleccionado:</span>
          <span id="label-producto-elegido" class="text-xs font-black text-[#F59E0B] truncate block font-sans">
            Ningún producto seleccionado
          </span>
        </div>

        <!-- Selector Cantidad -->
        <div class="md:col-span-3 flex items-center bg-[#182338] border border-[#26354D] rounded-xl px-2 py-1 justify-between">
          <button type="button" onclick="modificarCantidad(-1)" class="w-8 h-8 rounded-lg bg-[#121A2A] text-white font-bold hover:bg-[#23324A] flex items-center justify-center cursor-pointer">-</button>
          <input type="number" name="cantidad" id="input-cantidad-modal" value="1" min="1" max="50" required
                 class="w-12 bg-transparent text-center font-bold text-white text-xs focus:outline-none">
          <button type="button" onclick="modificarCantidad(1)" class="w-8 h-8 rounded-lg bg-[#121A2A] text-white font-bold hover:bg-[#23324A] flex items-center justify-center cursor-pointer">+</button>
        </div>

        <!-- Notas Rápidas -->
        <div class="md:col-span-3">
          <input type="text" name="notas" placeholder="Ej: Sin sal, bien cocido..." 
                 class="w-full bg-[#182338] border border-[#26354D] rounded-xl px-3 py-2.5 text-white text-xs focus:outline-none focus:border-[#F59E0B] font-sans">
        </div>

        <!-- Botón Agregar -->
        <div class="md:col-span-2">
          <button type="submit" 
                  id="btn-confirmar-agregar-plato" 
                  disabled
                  class="w-full py-2.5 rounded-xl bg-[#10B981] text-[#0D1322] font-mono text-xs font-black uppercase hover:brightness-110 active:scale-95 transition disabled:opacity-40 disabled:cursor-not-allowed shadow-md shadow-[#10B981]/20 cursor-pointer">
            Añadir
          </button>
        </div>

      </div>
    </form>

  </div>
</div>

<script>
let mesaSeleccionadaGlobal = null;

/* 1. Filtrado instantáneo en la misma pantalla según el select */
function filtrarMesasPorZona(criterio) {
  const tarjetas = document.querySelectorAll('.tarjeta-mesa');
  const btnTodas = document.getElementById('btn-filtro-todas');

  if (!criterio) {
    tarjetas.forEach(t => t.classList.remove('hidden'));
    btnTodas.className = "px-5 py-2 rounded-full font-mono text-xs font-bold uppercase tracking-wider bg-[#F59E0B] text-[#0D1322] shadow-md shadow-[#F59E0B]/20 transition cursor-pointer";
    document.getElementById('select-zona').value = "";
    return;
  }

  btnTodas.className = "px-5 py-2 rounded-full font-mono text-xs font-bold uppercase tracking-wider bg-[#182338] border border-[#2E3F5C] text-white hover:border-[#F59E0B] transition cursor-pointer";

  const partes = criterio.split(':');
  const tipo = partes[0];
  const valor = partes[1].toLowerCase();

  tarjetas.forEach(t => {
    let coincide = false;
    if (tipo === 'zona') {
      coincide = t.getAttribute('data-zona') === valor;
    } else if (tipo === 'estado') {
      coincide = t.getAttribute('data-estado') === valor;
    }

    if (coincide) {
      t.classList.remove('hidden');
    } else {
      t.classList.add('hidden');
    }
  });
}

/* 2. Seleccionar Mesa y cargar sus platos en el panel */
function seleccionarMesa(mesa, cardElement) {
  mesaSeleccionadaGlobal = mesa;

  document.querySelectorAll('.tarjeta-mesa').forEach(t => {
    t.classList.remove('ring-4', 'ring-white/40', 'scale-[1.02]');
  });
  if (cardElement) {
    cardElement.classList.add('ring-4', 'ring-white/40', 'scale-[1.02]');
  }

  document.getElementById('panel-mesa-titulo').innerText = 'Mesa #' + mesa.numero_mesa;
  document.getElementById('accion-mesa-id-cuenta').value = mesa.mesa_id;
  document.getElementById('modal-mesa-id-plato').value = mesa.mesa_id;

  // Actualizar IDs para el formulario de cocina
  document.getElementById('accion-mesa-id-cocina').value = mesa.mesa_id;
  document.getElementById('accion-pedido-id-cocina').value = mesa.pedido_id || '';

  const badge = document.getElementById('panel-mesa-badge');
  const pedidoTexto = document.getElementById('panel-mesa-pedido');
  const estado = (mesa.mesa_estado || 'disponible').toLowerCase();

  if (mesa.pedido_id) {
    pedidoTexto.innerText = 'Pedido #' + String(mesa.pedido_id).padStart(4, '0');
  } else {
    pedidoTexto.innerText = 'Sin pedido activo';
  }

  if (estado === 'precuenta') {
    badge.className = 'px-4 py-1 rounded-full font-mono text-xs font-bold bg-[#F59E0B] text-[#0D1322] border border-[#F59E0B]';
    badge.innerText = 'PRECUENTA';
  } else if (estado === 'ocupada') {
    badge.className = 'px-4 py-1 rounded-full font-mono text-xs font-bold bg-rose-500/20 text-rose-400 border border-rose-500/40';
    badge.innerText = 'OCUPADA';
  } else {
    badge.className = 'px-4 py-1 rounded-full font-mono text-xs font-bold bg-[#10B981]/20 text-[#10B981] border border-[#10B981]/40';
    badge.innerText = 'DISPONIBLE';
  }

  // Cargar ítems
  const contenedorItems = document.getElementById('panel-mesa-items');
  contenedorItems.innerHTML = '';

  if (mesa.items && mesa.items.length > 0) {
    mesa.items.forEach(item => {
      const fila = document.createElement('div');
      fila.className = 'flex items-start justify-between font-mono pb-2 border-b border-[#23324A]/40';
      const precioItem = parseFloat(item.subtotal || (item.cantidad * item.precio_unitario)) || 0;
      fila.innerHTML = `
        <div>
          <span class="font-bold text-sm text-white">${item.cantidad}X ${item.producto_nombre}</span>
          ${item.notas ? `<p class="text-xs text-slate-400 mt-0.5 font-sans">Nota: ${item.notas}</p>` : ''}
        </div>
        <span class="font-bold text-sm text-white">
          $ ${parseInt(precioItem).toLocaleString('es-CO')}
        </span>
      `;
      contenedorItems.appendChild(fila);
    });
  } else {
    contenedorItems.innerHTML = '<p class="font-mono text-xs text-slate-500 py-16 text-center">No hay platos añadidos en esta mesa.</p>';
  }

  const total = parseInt(mesa.subtotal_pedido || 0);
  document.getElementById('panel-mesa-subtotal').innerText = '$ ' + total.toLocaleString('es-CO');

  // Habilitar botón de agregar platos
  document.getElementById('btn-abrir-modal-plato').disabled = false;

  // Habilitar pedir cuenta y enviar cocina
  const btnCuenta = document.getElementById('btn-pedir-cuenta');
  const btnCocina = document.getElementById('btn-enviar-cocina');

  if (mesa.pedido_id && total > 0) {
    btnCuenta.disabled = false;
    btnCocina.disabled = false;
  } else {
    btnCuenta.disabled = true;
    btnCocina.disabled = true;
  }
}

/* 3. Modal Visual de Catálogo con Fotos */
function abrirModalAgregarPlato() {
  if (!mesaSeleccionadaGlobal) return;
  document.getElementById('modal-titulo-agregar').innerText = 'Añadir Producto a Mesa #' + mesaSeleccionadaGlobal.numero_mesa;
  document.getElementById('modal-producto-id-seleccionado').value = '';
  document.getElementById('label-producto-elegido').innerText = 'Ningún producto seleccionado';
  document.getElementById('input-cantidad-modal').value = 1;
  document.getElementById('btn-confirmar-agregar-plato').disabled = true;
  document.querySelectorAll('.card-producto-item').forEach(c => c.classList.remove('ring-2', 'ring-[#10B981]', 'border-[#10B981]'));
  
  document.getElementById('modal-agregar-plato').classList.remove('hidden');
}

function cerrarModalAgregarPlato() {
  document.getElementById('modal-agregar-plato').classList.add('hidden');
}

function seleccionarProductoCard(id, nombre, precio, elemento) {
  document.getElementById('modal-producto-id-seleccionado').value = id;
  document.getElementById('label-producto-elegido').innerText = nombre + ' ($ ' + parseInt(precio).toLocaleString('es-CO') + ')';
  document.getElementById('btn-confirmar-agregar-plato').disabled = false;

  document.querySelectorAll('.card-producto-item').forEach(c => {
    c.classList.remove('ring-2', 'ring-[#10B981]', 'border-[#10B981]');
  });
  elemento.classList.add('ring-2', 'ring-[#10B981]', 'border-[#10B981]');
}

function filtrarCatalogoModal(termino) {
  const query = termino.toLowerCase().trim();
  const tarjetas = document.querySelectorAll('.card-producto-item');
  tarjetas.forEach(t => {
    const nombre = t.getAttribute('data-nombre');
    if (nombre.includes(query)) {
      t.classList.remove('hidden');
    } else {
      t.classList.add('hidden');
    }
  });
}

function modificarCantidad(delta) {
  const input = document.getElementById('input-cantidad-modal');
  let val = parseInt(input.value) || 1;
  val = Math.max(1, Math.min(50, val + delta));
  input.value = val;
}

// Mantener mesa seleccionada tras recarga o acción
document.addEventListener('DOMContentLoaded', () => {
  const mesaIdObjetivo = <?= $mesaParamId ?>;
  if (mesaIdObjetivo > 0) {
    const tarjeta = document.querySelector(`.tarjeta-mesa[data-mesa-id="${mesaIdObjetivo}"]`);
    if (tarjeta) tarjeta.click();
  }
});
</script>