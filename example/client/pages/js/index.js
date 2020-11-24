
import Api from '../../modules/Api.js';
(async () => {
    let _api = new Api({ model: "api" });
    await _api.start();
    console.log(_api)

    let modelsList = document.getElementById('modelList');
    _api.config.models.forEach(model => {
        console.log(model);
        let li = document.createElement("li");
        let a = document.createElement("a");
        a.href = "#";
        a.innerHTML = model.name;
        li.appendChild(a);
        li.addEventListener('click', () => { showActions(model) });
        modelsList.appendChild(li);
    });
})();

function showActions(model) {
    let modelsList = document.getElementById('actionList');

    while (modelsList.firstChild) {
        modelsList.removeChild(modelsList.lastChild);
    }
    model.actions.forEach(action => {
        let li = document.createElement("li");
        let a = document.createElement("a");
        a.href = "#";
        a.innerHTML = action.name;
        li.appendChild(a);
        modelsList.appendChild(li);
    });
}

function showActionDetail(action) {
    let modelsList = document.getElementById('actionList');

    model.actions.forEach(action => {
        let li = document.createElement("li");
        let a = document.createElement("a");
        a.href = "#";
        a.innerHTML = action.name;
        li.appendChild(a);
        modelsList.appendChild(li);
    });
}