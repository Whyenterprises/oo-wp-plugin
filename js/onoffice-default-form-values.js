onOffice = onOffice || {};

// polyfill
if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = Array.prototype.forEach;
}

(function() {
    document.querySelectorAll('select[name=language-language].onoffice-input').forEach(function(element) {
        element.backupLanguageSelection = {};
        var mainInput = element.parentElement.parentElement.querySelector('input[name^=oopluginfieldconfigformdefaultsvalues-value].onoffice-input');
        var fieldname = element.parentElement.parentElement.parentElement.querySelector('span.menu-item-settings-name').textContent;
        mainInput.name = 'defaultvalue-lang[' + fieldname + '][native]';

        element.addEventListener('change', function(event) {
            var value = event.srcElement.value || '';

            if (value !== '') {
                var clone = generateClone(mainInput, value);
                var label = generateLabel(event.srcElement, clone);
                var deleteButton = generateDeleteButton(event.srcElement, value);
                var paragraph = generateParagraph(label, clone, deleteButton);

                element.backupLanguageSelection[event.srcElement.selectedOptions[0].value] = event.srcElement.selectedOptions[0];
                event.srcElement.options[event.srcElement.selectedIndex] = null;

                mainInput.parentNode.parentNode.insertBefore(paragraph, event.srcElement.parentNode);
            }
        });

        function generateClone(mainInput, language) {
             var clone = mainInput.cloneNode(true);
                clone.id = 'defaultvalue-lang-' + language;
                clone.name = 'defaultvalue-lang[' + fieldname + '][' + language + ']';
                clone.style.width = '100%';
                clone.style.marginLeft = '20px';
                clone.value = '';
              return clone;
        }

        function generateLabel(srcElement, clone) {
            var label = document.createElement('label');
                label.classList = ['howto'];
                label.htmlFor = clone.id;
                label.style.minWidth = 'min-content';
                label.textContent = srcElement.selectedOptions[0].text;
            return label;
        }

        function generateDeleteButton(srcElement, language) {
            var deleteButton = document.createElement('span');
            deleteButton.id = 'deleteButtonLang-' + language;
            deleteButton.className = 'dashicons dashicons-dismiss deleteButtonLang';
            deleteButton.targetLanguage = language;
            deleteButton.style.display = 'block';
            deleteButton.style.verticalAlign = 'middle';

            deleteButton.addEventListener('click', function(deleteEvent) {
                var restoreValue = element.backupLanguageSelection[deleteEvent.srcElement.targetLanguage];
                srcElement.options.add(restoreValue);
                srcElement.selectedIndex = 0;
                deleteEvent.srcElement.parentElement.remove();
            });
            return deleteButton;
        }

        function generateParagraph(label, clone, deleteButton) {
            var paragraph = document.createElement('p');
            paragraph.classList = ['wp-clearfix'];
            paragraph.style.display = 'inline-flex';
            paragraph.style.width = '100%';
            paragraph.appendChild(label);
            paragraph.appendChild(clone);
            paragraph.appendChild(deleteButton);
            return paragraph;
        }
    });
})();