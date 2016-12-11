window.autoinput = (function (jsonObject) {
    var value = '';
    var preset = 0;
    var hint = '';
    var name = '';
    var predefined = [];
    var properties = [];

    var _this = window.jui.views.input;
    var _tools = window.jui.tools;

    var parse = function (jsonObject) {
        if (!_tools.empty(jsonObject['name'])) {
            _this.setName(jsonObject['name']);

            if(!_tools.empty(jsonObject['value'])) {
                _this.setValue(jsonObject['value']);
            }

            if (!_tools.empty(jsonObject['predefined'])) {
                _this.setPredefined(jsonObject['predefined']);
            }

            properties = jsonObject;
        }

        return _this;
    };

    _this.setValue = function (pValue) {
        value = pValue;
    };


    _this.setName = function (pName) {
        name = pName;
    };

    _this.setPredefined = function(pArray) {
        predefined = pArray;
    };

    _this.getDomElement = function () {

        if(!_tools.empty(name)) {

            var added = [];

            var container = document.createElement('div');
            container.className = 'jui-autoinput';
            container.style.border = '1px solid #666666';
            container.style.padding = '5px';
            container.style.cursor = 'text';

            var datalist = document.createElement('DATALIST');
            datalist.id = _tools.getHtmlId();
                if(_tools.isArray(predefined))
                for(var i = 0, x = predefined.length; i < x; i++) {
                    datalist.appendChild(getOption(predefined[i]));
                }
            container.appendChild(datalist);

            var input = document.createElement('input');
            input.setAttribute('type', 'text');
            input.style.display = 'none';
            container.appendChild(input);

            var retval = document.createElement('input');
            retval.setAttribute('type', 'text');
            retval.style.border = 'none';
            retval.style.outline = 'none';
            retval.setAttribute('list', datalist.id);

            container.addEventListener('click', function() {
                retval.focus();
            });



            function createTag(value) {
                var tag = document.createElement('span');
                tag.innerHTML = value;
                tag.style.display = 'inline-block';
                tag.style.backgroundColor = '#11FF11';
                tag.style.borderRadius = '10px';
                tag.style.padding = '2px 10px 2px 10px';
                tag.style.margin = '2px 5px 2px 5px';
                tag.style.userSelect = 'none';

                tag.addEventListener('click', function() {
                    var index = added.indexOf(value);
                    if (index > -1) {
                        added.splice(index, 1);
                    }

                    container.removeChild(tag);

                    _this.updateInput(input, added);
                });

                return tag;
            }




            retval.addEventListener('keydown', function(event) {
                if((event.keyCode == 13 || event.keyCode == 9) && retval.value != '' && added.indexOf(retval.value) == -1) {
                    event.preventDefault();

                    var value =  retval.value;


                    container.insertBefore(createTag(value), retval);

                    added.push(retval.value);

                    retval.value = '';

                    _this.updateInput(input, added);

                    return false;
                }

                retval.size = retval.value.length+1;
            });

            container.appendChild(retval);

            if(_tools.isArray(value))
                for(var j = 0, y = value.length; j < y; j++) {
                    added.push(value[j]);
                    container.insertBefore(createTag(value[j]), retval);
                    _this.updateInput(input, added);
                }

            window.jui.registerSubmitElement(name, input);

            window.jui.views.view.addProperties(container, properties);

            return window.jui.views.view.addInputProperties(container, properties);
        }

        return null;
    };

    _this.updateInput = function(input, added) {
        console.log('input', input.value);
        input.value = JSON.stringify(added);
    };

    function getOption(value) {
        var retval = document.createElement('option');
        retval.value = value;
        retval.innerHTML = value;

        return retval;
    }

    return parse(jsonObject);
});