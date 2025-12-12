import { Validate } from "./Validate.js";
import { Requests } from "./Requests.js";
const Salvar = document.getElementById('salvar');

Salvar.addEventListener('click', async () => {
    Validate.SetForm('form').Validate();
    const response = Requests.SetForm('form').Post('/cliente/insert');
    console.log(response);
});