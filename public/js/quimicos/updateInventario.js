document
  .getElementById("formUpdateInventario")
  .addEventListener("submit", async function (e) {
    e.preventDefault();
    mostrarCarga();

    const form = document.getElementById("formUpdateInventario");
    const formData = new FormData(form);

    const formObj = {};
    formData.forEach((value, key) => {
      if (formObj[key] === undefined) {
        formObj[key] = value;
      } else if (Array.isArray(formObj[key])) {
        formObj[key].push(value);
      } else {
        formObj[key] = [formObj[key], value];
      }
    });

    const action = "actualizarInventario";

    try {
      const response = await fetch(
        "../../Handler/quimicos/quimicosHandler.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            action: action,
            ...formObj,
          }),
        },
      );

      const resultado = await response.json();

      ocultarCarga();

      if (resultado.success) {
        notification(
          "success",
          "Se actualizó el inventario del químico.",
          2000,
        );
        setTimeout(function () {
          if (Fancybox.getInstance()) {
            Fancybox.getInstance().close();
          }

          location.reload();
        }, 2000);
      } else {
        notification("error", resultado.message, 4000);
      }
    } catch (error) {
      ocultarCarga();
      notification("error", error, 3000);
    }
  });

document.addEventListener("click", function (e) {
  if (e.target.matches(".carousel__button.is-close")) {
    location.reload();
  }
});
