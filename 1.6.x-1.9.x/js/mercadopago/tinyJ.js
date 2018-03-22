function ElemContainer(elem){
    if(!elem){
        throw "Invalid element";
    }
    this.elem = elem;
    this.displayConfig = {
        hide : 'none',
        show : 'block'
    };

    this.eventNames = {
        click : 'click',
        blur : 'blur',
        change : 'change',
        focus : 'focus',
        focusout: 'focusout',
        keyup: 'keyup'
    };

    this.getElem = function(query){
        if(!isEmptyValue(query)){
            return TinyJ(query, this.elem);
        }
        return this.elem;
    };

    this.isChecked = function(selector){
        return this.getElem().checked;
    };


    this.getSelectedOption = function(){
        return TinyJ(this.getElem()[this.getElem().options.selectedIndex]);
    };

    this.id = function(id){
        if(isEmptyValue(id)){
            return this.getElem().id;
        }
        this.getElem().id = String(id);
        return this;
    };

    this.attribute = function(name, value){
        if(isEmptyValue(value)){
            return this.getElem().getAttribute(name);
        }
        this.getElem().setAttribute(name, String(value));
        return this;
    };

    this.html = function(value){
        if(isEmptyValue(value)){
            return this.getElem().innerHTML;
        }
        this.getElem().innerHTML = String(value);
        return this;
    };

    this.empty = function(){
        return this.html("");
    };

    this.val = function (val){
        if(isEmptyValue(val)){
            return this.getElem().value;
        }
        this.getElem().value = String(val);
        return this;
    };

    this.removeAttribute = function(name, value){
        this.getElem().removeAttribute(name, value);
        return this;
    };

    this.hide = function (){
        this.getElem().style.display = this.displayConfig.hide;
        return this;
    };

    this.show = function (){
        this.getElem().style.display = this.displayConfig.show;
        return this;
    };

    this.click = function(handler){
        this.on(this.eventNames.click, handler);
        return this;
    };

    this.focusout = function(handler){
        this.on(this.eventNames.focusout, handler);
        return this;
    };

    this.keyup = function(handler){
        this.on(this.eventNames.keyup, handler);
        return this;
    };

    this.blur = function(handler){
        this.on(this.eventNames.blur, handler);
        return this;
    };

    this.change = function(handler){
        this.on(this.eventNames.change, handler);
        return this;
    };

    this.focus = function(handler){
        this.on(this.eventNames.focus, handler);
        return this;
    };

    this.addClass = function(name){
        this.getElem().className = (this.getElem().className + " " + name).trim();
        return this;
    };

    this.removeClass = function(name){
        this.getElem().className = (this.getElem().className.replace(new RegExp(name.trim(), 'g'), '')).trim();
        return this;
    };

    this.disable = function(){
        this.getElem().disabled = true;
        return this;
    };

    this.enable = function(){
        this.removeAttribute('disabled');
        return this;
    };

    this.on = function(eventName, handler){
        addEvent(this.getElem(), eventName, handler);
        return this;
    };

    this.appendChild = function(child){
        this.getElem().appendChild(child);
        return this;
    };

    function isEmptyValue(value){
        return (value === undefined || value === null);
    }

    function addEvent(el, eventName, handler){
        if (el.addEventListener) {
            el.addEventListener(eventName, handler);
        } else {
            el.attachEvent('on' + eventName, function(){
                handler.call(el);
            });
        }
    }
}

var TinyJ = function(elemDescriptor, parentElem){
    if(parentElem){
        return getElems(elemDescriptor, parentElem);
    }
    if(elemDescriptor){
        if(typeof elemDescriptor === 'object'){
            return new ElemContainer(elemDescriptor);
        }
        return getElems(elemDescriptor);
    }

    function getElems(elemDescriptor, parentElem){
        var parent = parentElem ? parentElem : document;
        var elements = parent.querySelectorAll(elemDescriptor);
        if(elements.length === 0){
            return [];
        }
        if(elements.length === 1){
            return new ElemContainer(elements[0]);
        }
        result = [];
        Array.prototype.forEach.call(elements, function(element, index, array){result.push(new ElemContainer(element));})
        return result;
    }
};

if (!String.format) {
    String.format = function(format) {
        var args = Array.prototype.slice.call(arguments, 1);
        return format.replace(/{(\d+)}/g, function(match, number) {
            return typeof args[number] != 'undefined'
                ? args[number]
                : match
                ;
        });
    };
}