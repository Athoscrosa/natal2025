import { Validate } from "./Validate.js";
import { Requests } from "./Requests.js";

const salvar = document.getElementById('insert');

$('#cpf').inputmask({ "mask": ["999.999.999-99"] });

salvar.addEventListener('click', async () => {
    const IsValid = Validate.SetForm('form').Validate();
    if (!IsValid) {
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Por favor verifique os campos obrigatório, e preencha corretamente!",
            showConfirmButton: false,
            timer: 4000
        });
        return;
    }
    const response = await Requests.SetForm('form').Post('/usuario/insert');
    if (!response.status) {
        Swal.fire({
            position: "center",
            icon: "error",
            title: response.msg,
            showConfirmButton: false,
            timer: 4000
        });
        return;
    }
    Swal.fire({
        position: "center",
        icon: "success",
        title: response.msg,
        showConfirmButton: false,
        timer: 3000
    });
    return;
});