<!--cocinero-->
<main class="flex-1 w-full max-w-[1550px] mx-auto p-4 sm:p-6 font-mono select-none flex flex-col lg:flex-row gap-6">
  <section class="flex-1 flex flex-col">
    
    <!--Ver Historial-->
    <div class="mb-6">
      <a href="<?php echo APP_URL; ?>historialPedidos" 
         class="inline-block px-5 py-2 rounded-full font-bold text-xs bg-[#182338] text-white hover:bg-[#F59E0B] shadow-md">
        Ver historial
      </a>
    </div>

    <!--Comandas-->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

        <!-- Tarjeta de Comanda -->
        <div class="bg-[#121A2A] border border-[#23324A] rounded-2xl overflow-hidden shadow-xl flex flex-col justify-between h-[360px]">
          
          <!-- Encabezado de la Tarjeta (Gris azulado con contraste) -->
          <div class="bg-[#1C283E] px-4 py-3 border-b border-[#23324A]">
            <div class="flex items-center justify-between">
              <span class="text-sm font-extrabold text-white">Mesa #2</span>
              <span class="text-xs font-bold>1</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-0.5">
              Carlos
            </div>
          </div>

          <!--Cuerpo-->
          <div class="p-4 flex-1 space-y-3 overflow-y-auto">
              <div>
                <div class="text-sm font-bold text-white leading-tight">
                  coca
                </div>
                  <div class="text-[11px] text-slate-400 font-sans mt-0.5">
                    Nota: sin nah
                  </div>
              </div>
          </div>

          <!--Botón de Estado-->
          <div class="p-4 pt-0">
            <a href="#" 
               class="block w-full py-2.5 rounded-full font-mono text-xs font-bold text-center uppercase tracking-wide transition active:scale-95 bg-greenAccent">
              Listo
            </a>
          </div>

        </div>
    </div>

  </section>

  <div class="hidden lg:block w-[1px] bg-[#23324A] self-stretch mx-2"></div>

  <!--Notificaciones-->
  <aside class="w-full lg:w-[360px] flex flex-col gap-4">
    
    <!--Encabezado-->
    <div class="flex items-center justify-between">
      <h3 class="text-base font-extrabold text-white tracking-wide">Notificaciones</h3>
      <a href="<?php echo APP_URL; ?>notiCocinero" 
         class="px-4 py-1 rounded-full text-xs font-bold bg-[#F59E0B] text-[#0D1322] hover:brightness-110 transition shadow-sm">
        Ver todas
      </a>
    </div>

    <!--Lista-->
    <div class="space-y-3">
        <div class="h-11 rounded-full bg-[#121A2A] border border-[#23324A] flex items-center px-4 text-xs text-slate-400 hover:border-[#F59E0B]/40 transition cursor-default">
            <span>HOLA</span>
        </div>
    </div>

  </aside>

</main>