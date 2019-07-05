(function() {
    // Get keys as the column headings for the first bib item table.
    var keys = [];
    var header = document.querySelector('tr.bibItemsHeader')

    if (header) {
        Array.prototype.forEach.call(
            header.querySelectorAll('th.bibItemsHeader'),
            function(element) {
                keys.push(element.textContent.trim());
            }
        );
    }

    if (!keys) {
        return;
    }

    // Loop through each bib item.
    Array.prototype.forEach.call(
        document.querySelectorAll('.bibItemsEntry'),
        function(element, index) {
            var cell = {}, text = {};

            // Retrieve the cell and text from the entry for each column.
            for (var count = 0; count < keys.length; count++) {
                cell[keys[count]] = element.querySelectorAll('td')[count];
                text[keys[count]] = '';

                if (!cell[keys[count]]) {
                    continue;
                }

                Array.prototype.forEach.call(
                    cell[keys[count]].childNodes,
                    function(node) {
                        if (node.nodeType !== 1 && node.nodeType !== 3) {
                            return;
                        }

                        if (typeof node.classList !== 'undefined') {
                            if (node.classList.contains('button')) {
                                return;
                            }
                        }

                        text[keys[count]] += node.textContent;
                    }
                );

                text[keys[count]] = text[keys[count]].trim();
            }

            // Remove call numbers from items available on the Internet.
            if (text['Location'] === 'Internet') {
                cell['Call Number'].innerHTML = '';
                return;
            }

            // Ignore entries without call numbers.
            if (text['Call Number'] === '') {
                return;
            }

            // If the item has a call number, append the Map It button.
            var button = document.createElement('a');
            var href = 'http://lib.bgsu.edu/catalog/stackmap/search.php?' +
                'loc_arr=' + encodeURIComponent(text['Location']) + '&' +
                'call_arr=' + encodeURIComponent(text['Call Number']);

            button.setAttribute('href', href.replace(/%20/g, '+'));
            button.setAttribute('class', 'button button-small button-primary');
            button.setAttribute(
                'aria-label',
                'Map to ' +
                    text['Location'] + ' ' +
                    text['Call Number'] + ' ' +
                    text['Volume']
            );

            button.innerHTML = 'Map It';
            button.addEventListener('click', function(event) {
                // Set button to load a popup window when clicked.
                window.open(
                    this.href,
                    'mapit',
                    'width=780,height=780,' +
                    'menubar=1,resizable=1,scrollbars=1'
                );

                event.preventDefault();
            });

            cell['Location'].insertBefore(button, cell['Location'].firstChild);
        }
    );
})();
