async function obtenerFechaHabilesAntes(fechaBase, cantidadDiasHabiles) {
  const obtenerFestivosColombiaDesdeAPI = async (anio) => {
    const cacheKey = `festivos_${anio}`;
    const cache = localStorage.getItem(cacheKey);
    if (cache) return JSON.parse(cache);

    const response = await fetch(`https://date.nager.at/api/v3/PublicHolidays/${anio}/CO`);
    if (!response.ok) throw new Error('No se pudo obtener los festivos');

    const data = await response.json();
    const festivos = data.map(f => f.date); // ["YYYY-MM-DD"]
    localStorage.setItem(cacheKey, JSON.stringify(festivos));
    return festivos;
  };

  const esDiaHabil = (fecha, festivos) => {
    const dia = fecha.getDay(); // 0: domingo, 6: sábado
    const fechaStr = fecha.toISOString().split('T')[0];
    return dia !== 0 && dia !== 6 && !festivos.includes(fechaStr);
  };

  const formatearFecha = (fecha) => fecha.toLocaleDateString('sv-SE'); // YYYY-MM-DD

  let fecha = new Date(fechaBase);
  fecha.setHours(0, 0, 0, 0);
  let anio = fecha.getFullYear();
  let festivos = await obtenerFestivosColombiaDesdeAPI(anio);

  let contador = 0;
  while (contador < cantidadDiasHabiles) {
    fecha.setDate(fecha.getDate() - 1);
    const nuevoAnio = fecha.getFullYear();
    if (nuevoAnio !== anio) {
      anio = nuevoAnio;
      festivos = await obtenerFestivosColombiaDesdeAPI(anio);
    }
    if (esDiaHabil(fecha, festivos)) contador++;
  }

  return formatearFecha(fecha);
}
