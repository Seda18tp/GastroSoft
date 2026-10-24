<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('user-menu-btn');
        const dropdown = document.getElementById('user-dropdown');

        if (btn && dropdown) {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', () => {
                if (!dropdown.classList.contains('hidden')) {
                    dropdown.classList.add('hidden');
                }
            });
        }
    });
    //usuarios-view
    function abrirModalUsuario(u) {
    const modal = document.getElementById('modal-usuario');
    const passInput = document.getElementById('form-user-pass');
    const passHint = document.getElementById('hint-password');

    if (u) {
        document.getElementById('modal-titulo-usuario').innerText = 'Editar Usuario';
        document.getElementById('form-user-id').value = u.id;
        document.getElementById('form-user-nombre').value = u.nombre_completo || u.nombre || '';
        document.getElementById('form-user-email').value = u.email || '';
        document.getElementById('form-user-rol').value = u.rol_id || u.id_rol || '';
        document.getElementById('form-user-estado').value = (u.estado == '1' || u.estado == 'Activo') ? 'Activo' : 'Inactivo';
        passInput.required = false;
        passInput.value = '';
        passHint.classList.remove('hidden');
    } else {
    document.getElementById('modal-titulo-usuario').innerText = 'Nuevo Usuario';
    document.getElementById('form-user-id').value = 0;
    document.getElementById('form-user-nombre').value = '';
    document.getElementById('form-user-email').value = '';
    document.getElementById('form-user-rol').value = '';
    document.getElementById('form-user-estado').value = 'Activo';
    passInput.required = true;
    passInput.value = '';
    passHint.classList.add('hidden');
    }

    modal.classList.remove('hidden');
    }

    function cerrarModalUsuario() {
    document.getElementById('modal-usuario').classList.add('hidden');
    }
    //nuevo plato
    document.getElementById('input-imagen-nuevo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(evt) {
        document.getElementById('img-preview-nuevo').src = evt.target.result;
        }
        reader.readAsDataURL(file);
    }
    });
    //miPerfil
    function previewImg(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('img-vista-previa').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    //inventario
    function abrirModalAjuste(item) {
    const modal = document.getElementById('modal-ajuste');
    
    if (item) {
        document.getElementById('modal-titulo').innerText = 'Modificar Stock';
        document.getElementById('form-inventario-id').value = item.inventario_id;
        document.getElementById('form-producto-nombre').value = item.producto_nombre;
        document.getElementById('form-stock-actual').value = item.stock_actual;
        document.getElementById('form-stock-minimo').value = item.stock_minimo;
        document.getElementById('form-und-medida').value = item.und_medida;
    }
    
    modal.classList.remove('hidden');
    }

    function cerrarModalAjuste() {
    document.getElementById('modal-ajuste').classList.add('hidden');
    }
    //editar plato
    document.getElementById('input-imagen').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(evt) {
        document.getElementById('img-preview').src = evt.target.result;
        }
        reader.readAsDataURL(file);
    }
    });
    //cerrarCaja
    function calcularDiferencia(valorRealStr) {
    const esperado = parseFloat(document.getElementById('monto_esperado').value) || 0;
    const real = parseFloat(valorRealStr) || 0;
    const dif = real - esperado;

    const textoDif = document.getElementById('texto-diferencia');
    const badge = document.getElementById('badge-estado');
    const contenedor = document.getElementById('box-diferencia');

    // Formato de moneda
    const difFormateada = Math.abs(dif).toLocaleString('es-CO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    badge.classList.remove('hidden');

    if (dif === 0) {
        textoDif.innerText = '$ 0.00';
        contenedor.className = "w-full bg-[#162032] border border-[#26354D] rounded-xl px-4 py-3 text-sm font-bold text-[#10B981] flex items-center justify-between";
        badge.className = "text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-[#10B981]/15 text-[#10B981] border border-[#10B981]/30";
        badge.innerText = 'Exacto';
    } else if (dif > 0) {
        textoDif.innerText = '+ $ ' + difFormateada;
        contenedor.className = "w-full bg-[#162032] border border-[#26354D] rounded-xl px-4 py-3 text-sm font-bold text-sky-400 flex items-center justify-between";
        badge.className = "text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-sky-500/15 text-sky-400 border border-sky-500/30";
        badge.innerText = 'Sobrante';
    } else {
        textoDif.innerText = '- $ ' + difFormateada;
        contenedor.className = "w-full bg-[#162032] border border-[#26354D] rounded-xl px-4 py-3 text-sm font-bold text-rose-500 flex items-center justify-between";
        badge.className = "text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-rose-500/15 text-rose-400 border border-rose-500/30";
        badge.innerText = 'Faltante';
    }
    }
    //carta
    function filtrarPlatos(catId, btn) {
    document.querySelectorAll('.filtro-btn').forEach(b => {
        b.className = 'filtro-btn px-4 py-1.5 rounded-full font-bold transition bg-[#182338] border border-[#26354D] text-slate-300 hover:text-white';
    });
    btn.className = 'filtro-btn px-4 py-1.5 rounded-full font-bold transition bg-[#F59E0B] text-[#0D1322]';

    const tarjetas = document.querySelectorAll('.tarjeta-producto');
    let visibles = 0;

    tarjetas.forEach(t => {
        const cardCat = parseInt(t.getAttribute('data-categoria'));
        if (catId === 0 || cardCat === catId) {
        t.style.display = 'flex';
        visibles++;
        } else {
        t.style.display = 'none';
        }
    });

    let aviso = document.getElementById('sin-productos-js');
    if (visibles === 0) {
        if (!aviso) {
        const grid = document.getElementById('grid-productos');
        aviso = document.createElement('div');
        aviso.id = 'sin-productos-js';
        aviso.className = 'col-span-full py-16 text-center text-slate-500 font-mono text-xs';
        aviso.innerText = 'No hay productos disponibles en esta categoría.';
        grid.appendChild(aviso);
        }
    } else if (aviso) {
        aviso.remove();
    }
    }
    //caja
    function limpiarNumero(valor) {
    return parseFloat(valor.toString().replace(/,/g, '').replace(/\s/g, '')) || 0;
    }

    function recalcularCambio(valorStr) {
        const total = parseFloat(document.getElementById('total_cuenta').value) || 0;
        const recibido = limpiarNumero(valorStr);
        const cambio = Math.max(0, recibido - total);

        document.getElementById('texto-cambio').innerText = cambio.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
        });
        document.getElementById('cambio_calculado').value = cambio.toFixed(2);
    }

    function seleccionarMetodo(metodo) {
        const total = parseFloat(document.getElementById('total_cuenta').value) || 0;
        const inputRecibido = document.getElementById('dinero_recibido');

        if (metodo !== 'Efectivo') {
        inputRecibido.value = total.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        }
        recalcularCambio(inputRecibido.value);
    }

    function seleccionarMesaCobro(mesa, cardElement) {
        document.getElementById('panel-vacio').classList.add('hidden');
        document.getElementById('panel-cobro').classList.remove('hidden');

        document.getElementById('titulo-liquidacion').innerText = 'Liquidación Mesa #' + mesa.numero_mesa;
        document.getElementById('subtitulo-mesero').innerText = 'Mesero: ' + (mesa.mesero_nombre ? mesa.mesero_nombre : 'General');
        document.getElementById('badge-total-cuenta').innerText = '$ ' + parseInt(mesa.total).toLocaleString('es-CO');

        document.getElementById('form_mesa_id').value = mesa.mesa_id;
        document.getElementById('form_pedido_id').value = mesa.pedido_id;
        document.getElementById('total_cuenta').value = mesa.total;

        document.getElementById('dinero_recibido').value = parseFloat(mesa.total).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
        });
        
        document.getElementById('cambio_calculado').value = "0.00";
        document.getElementById('texto-cambio').innerText = "0.00";

        document.querySelectorAll('.tarjeta-mesa').forEach(t => {
        t.classList.remove('ring-2', 'ring-[#10B981]');
        });
        if (cardElement) {
        cardElement.classList.add('ring-2', 'ring-[#10B981]');
        }
    }
    //admin
    function abrirModalDetalleReserva(res) {
    document.getElementById('det-reserva-id').innerText = '#' + res.id;
    document.getElementById('form-reserva-id').value = res.id;
    document.getElementById('det-cliente').innerText = res.nombre_cliente;
    document.getElementById('det-telefono').innerText = res.telefono ? res.telefono : 'No registrado';
    document.getElementById('det-mesa').innerText = 'Mesa #' + res.numero_mesa;
    document.getElementById('det-personas').innerText = res.cantidad_personas + ' personas';
    
    const hInicio = res.hora_inicio ? res.hora_inicio.substring(0, 5) : '--:--';
    const hFin = res.hora_fin ? res.hora_fin.substring(0, 5) : '--:--';
    document.getElementById('det-horario').innerText = hInicio + ' a ' + hFin;
    
    document.getElementById('det-observaciones').innerText = res.observaciones ? res.observaciones : 'Sin observaciones especiales.';
    document.getElementById('form-reserva-estado').value = (res.estado || '').toLowerCase();

    document.getElementById('modal-detalle-reserva').classList.remove('hidden');
    }

    function cerrarModalDetalleReserva() {
    document.getElementById('modal-detalle-reserva').classList.add('hidden');
    }
</script>