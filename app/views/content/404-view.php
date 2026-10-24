<section class="bg-darkBg dark:bg-gray-900 mx-60">
  <div class="container min-h-screen px-6 py-12 mx-auto lg:flex lg:items-center lg:gap-12">
    <div class="wf-ull lg:w-1/2">
      <p class="text-sm font-medium text-blue-500 dark:text-blue-400">404 error</p>
      <h1 class="mt-3 text-2xl font-semibold text-gray-800 text-white md:text-3xl">Pagina no encontrada</h1>
      <p class="mt-4 text-gray-500 text-gray-400">Lo siento, La pagina que estas buscando no existe.</p>

      <div class="flex items-center mt-6 gap-x-3">
        <button class="flex items-center justify-center w-1/2 px-5 py-2 text-sm border rounded-lg gap-x-2 sm:w-auto bg-brandAccent dark:text-gray-200 border-brandAccent hover:bg-orange-300">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 rtl:rotate-180">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75L3 12m0 0l3.75-3.75M3 12h18" />
          </svg>
          <a href="<?php echo APP_URL; ?>">Regresar</a>
        </button>
      </div>
    </div>

    <div class="relative w-full mt-12 lg:w-1/2 lg:mt-0">
      <img class="w-full max-w-lg lg:mx-auto" src="<?php echo APP_URL?>app/img/404.svg" alt="">
    </div>
  </div>
</section>