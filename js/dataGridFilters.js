var filter = {
    getNames: function() {
        return {
            '==': 'is equal to',
            '=~': 'contains',
            '=<': 'is less than',
            '=>': 'is greater than',
            '=#': 'has element',
            '=@': 'Near'
        };
    },
    makePreviousSelectionBoxesUnselectable: function(
        filterCounter,
        filterAreaID,
        selectableColumns
    ) {
        var filterArea = document.getElementById(filterAreaID);
        if (filterCounter > 1)
        {
            filterBr = document.createElement('br');
            filterBr.clear = 'all';
        
            filterArea.appendChild(filterBr);

            for (var i = 1; i < filterCounter; i++)
            {
                var columnSelector = document.getElementById(filterAreaID+i+'columnName');
                columnSelector.disabled=true;

                //Take previously filtered columns out of the list of filterable columns.
                for (var i2 = 0; i2 < selectableColumns.length; i2++)
                {
                    if (selectableColumns[i2] == columnSelector.value)
                    {
                        selectableColumns.splice(i2, 1);
                        i2--;
                    }
                }
            }
        }
    }
};

filter.FilterFactory = {}
filter.FilterFactory.createFromPossibleOperatorType = function(possibleType) {
    if (getFilterColumnTypesFromOptionValue(possibleType) == '=@') {
        return new filter.NearZipCodeFilter();
    } else {
        return new filter.DefaultFilter();
    }
}

filter.Filter = function() {
}

filter.Filter.prototype.createFieldSelect = function(filterAreaID, filterCounter, selectableColumns) {
    var selectColumn = document.createElement('select');
    for (var i = 0; i < selectableColumns.length; i++)
    {
        selectColumn.appendChild(this.createOption(
            selectableColumns[i],
            getFilterColumnNameFromOptionValue(selectableColumns[i])
        ));
    }
    selectColumn.id = filterAreaID+filterCounter+'columnName';
    selectColumn.className = 'inputbox';
    return selectColumn;
}

filter.Filter.prototype.createOption = function(value, innerHtml) {
    var option = document.createElement('option');
    option.value = value;
    option.innerHTML = innerHtml;
    return option;
}

filter.Filter.prototype.createElement = function(tagName, properties, eventListeners) {
    var element = document.createElement(tagName);
    for (var property in properties) {
        element[property] = properties[property];
    }
    if (eventListeners) {
        for (var eventName in eventListeners) {
            element.addEventListener(eventName, eventListeners[eventName]);
        }
    }
    return element;
}

filter.DefaultFilter = function() {
}

filter.DefaultFilter.prototype = Object.create(filter.Filter.prototype);

filter.DefaultFilter.prototype.createOperatorSelect = function(filterAreaID, filterCounter) {
    var selectColumn = document.createElement('select');
    selectColumn.id = filterAreaID+filterCounter+'operator';
    selectColumn.className = 'inputbox';
    selectColumn.style.width='120px';
    return selectColumn;
}

filter.DefaultFilter.prototype.createSelectAreaChangeHandler = function(selectColumn, selectOperatorColumn) {
    var me = this;
    return function() {
        var possibleTypes = getFilterColumnTypesFromOptionValue(selectColumn.value);
        if (selectOperatorColumn.hasChildNodes() )
        {
            while (selectOperatorColumn.childNodes.length >= 1 )
            {
                selectOperatorColumn.removeChild( selectOperatorColumn.firstChild );       
            } 
        }
    
        for (var i = 0; i < possibleTypes.length; i+=2)
        {
            var possibleType = possibleTypes.substr(i,2);
            selectOperatorColumn.appendChild(
                me.createOption(
                    possibleType,
                    filter.getNames()[possibleType]
                )
            );
        }
    };
}

filter.DefaultFilter.prototype.createInputAreaChangeHandler = function(instanceName, filterAreaID, filterCounter) {
    return function() {
        addColumnToFilter(
            'filterArea' + instanceName, 
            getFilterColumnNameFromOptionValue(document.getElementById(filterAreaID+filterCounter+'columnName').value),
            document.getElementById(filterAreaID+filterCounter+'operator').value,
            document.getElementById(filterAreaID+filterCounter+'value').value
        ); 
    };
}

filter.DefaultFilter.prototype.createInputArea = function(filterAreaID, filterCounter, instanceName) {
    var inputArea = document.createElement('input');
    inputArea.id = filterAreaID+filterCounter+'value';
    inputArea.style.width='180px';
    var inputAreaChangeHandler = this.createInputAreaChangeHandler(instanceName, filterAreaID, filterCounter)
    if (inputArea.addEventListener) {
        inputArea.addEventListener('change', inputAreaChangeHandler, false);
     } else if (inputArea.attachEvent) {
        inputArea.attachEvent('onchange', inputAreaChangeHandler);
     }
     inputArea.className = 'inputbox';
    return inputArea;
}

filter.DefaultFilter.prototype.render = function(
    filterCounter,
    filterAreaID,
    selectableColumns,
    instanceName
) {
    var filterDiv = document.createElement('div');
    var selectColumn = this.createFieldSelect(filterAreaID, filterCounter, selectableColumns);
    filterDiv.appendChild(selectColumn);
    var operatorSelectColumn = this.createOperatorSelect(filterAreaID, filterCounter);
    filterDiv.appendChild(operatorSelectColumn);
    if (selectColumn.addEventListener) {
        selectColumn.addEventListener('change', this.createSelectAreaChangeHandler(selectColumn, operatorSelectColumn), false);
     } else if (selectColumn.attachEvent) {
        selectColumn.attachEvent('onchange', this.createSelectAreaChangeHandler(selectColumn, operatorSelectColumn));
     }
    var inputArea = this.createInputArea(filterAreaID, filterCounter, instanceName);
    filterDiv.appendChild(inputArea);
    filterDiv.style.float='left';
    this.createSelectAreaChangeHandler(selectColumn, operatorSelectColumn)();
    return filterDiv;
}

filter.NearZipCodeFilter = function() {
}

filter.NearZipCodeFilter.prototype = Object.create(filter.Filter.prototype);

filter.NearZipCodeFilter.prototype.render = function(
    filterCounter,
    filterAreaID,
    selectableColumns,
    instanceName
) {
    var filterDiv = document.createElement('div');
    var selectColumn = this.createFieldSelect(filterAreaID, filterCounter, selectableColumns);
    filterDiv.appendChild(selectColumn);
    /* Zipcode input area */
    filterDiv.appendChild(this.createElement(
        'span',
        {
            id: filterAreaID + filterCounter + 'zip1',
            innerHTML: 'Zipcode:'
        }
    )); 
    var inputAreaChangeHandlerZip = function() {
        addColumnToFilter('filterArea' + instanceName, 
            getFilterColumnNameFromOptionValue(document.getElementById(filterAreaID+filterCounter+'columnName').value),
            document.getElementById(filterAreaID+filterCounter+'operator').value,
            document.getElementById(filterAreaID+filterCounter+'zipInput1').value + ',' + document.getElementById(filterAreaID+filterCounter+'zipInput2').value
        );
    };
    filterDiv.appendChild(this.createElement(
        'input',
        {
            id: filterAreaID + filterCounter + 'zipInput1',
            style: 'width: 80px',
            className: 'inputbox,',
            innerHTML: 'Zipcode:'
        },
        {
            change: inputAreaChangeHandlerZip
        }
    ));
    filterDiv.appendChild(this.createElement(
        'span',
        {
            id: filterAreaID + filterCounter + 'zip2',
            innerHTML: 'Distance to Zipcode (Miles):'
        }
    ));
    filterDiv.appendChild(this.createElement(
        'input',
        {
            id: filterAreaID+filterCounter+'zipInput2',
            style: 'width: 80px;',
            className: 'inputbox,',
            innerHTML: 'Zipcode:',
            value: '25'
        },
        {
            change: inputAreaChangeHandlerZip
        }
    ));
    return filterDiv;
}