document
  .getElementById("formReturnCantidadConsumo")
  .addEventListener("submit", async function (e) {
    e.preventDefault();
    mostrarCarga();

    const form = document.getElementById("formReturnCantidadConsumo");
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

    const action = "updateCantidadConsumoSolicitado";

    try {
      const response = await fetch(
        "../../Handler/quimicos/solicitudesHandler.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            action: action,
            form: formObj,
          }),
        },
      );

      const resultado = await response.json();

      ocultarCarga();

      if (resultado.success) {
        notification(
          "success",
          "Se retornó la cantidad del consumo solicitado.",
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
