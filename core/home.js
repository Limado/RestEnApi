function Collapser(id) {
    
    uls = document.getElementsByTagName('ul');
    for (var i = 0; i < uls.length; i++) {
        if (uls[i].className == 'function')
            uls[i].style.display = 'none';
    }
    var x = document.getElementById(id);
    x.style.display = (x.style.display == "") ? 'none' : '';
}