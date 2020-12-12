
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
    /**
     * Delete last clicked model action list.
     */
    let actionList = document.getElementById('actionList');
    /**
     * Add new model action list.
     */
    while (actionList.firstChild) {
        actionList.removeChild(actionList.lastChild);
    }
    console.log(model)
    model.actions.forEach(action => {
        let li = document.createElement("li");
        let a = document.createElement("a");
        a.href = "#";
        a.innerHTML = action.name;
        li.appendChild(a);
        li.addEventListener('click', () => { showActionDetail(action) });
        actionList.appendChild(li);
    });
}

function showActionDetail(action) {
    console.log(action);

    let descriptionBox = document.getElementById('actionDescription');
    descriptionBox.innerHTML = '';
    descriptionBox.appendChild(jsonToUL(action));
}
function jsonToUL(json) {
    let ul = document.createElement('ul');

    for (let property in json) {
    
        let li = document.createElement('li');
        let h4 = document.createElement('h4');
        let p = document.createElement('p');
    
        if (typeof json[property] != 'object') {
            h4.innerHTML = property;
            p.innerHTML = json[property];
            li.appendChild(h4);
        } else {
            p.appendChild(jsonToUL(json[[property]]));
        }   
        li.appendChild(p);
        ul.appendChild(li);     
    }

    return ul;
}