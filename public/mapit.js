// Select all bib items available on the page.
jQuery('.bibItems tr.bibItemsEntry')
.on('click', '.map-it', function() {
    // Set any Map It button to load a popup window when clicked.
    window.open(
        this.href,
        'mapit',
        'width=800,height=650,menubar=1,resizable=1,scrollbars=1'
    );

    return false;
})
.each(function() {
    // Loop through each bib item.
    var element = jQuery(this), cell = {}, text = {},
        keys = ['Location', 'Call Number'];

    // Retrieve the cell and text from the cell for each column.
    for (var count = 0; count < keys.length; count++) {
        cell[keys[count]] = element.find('td:nth-child(' + (count + 1) + ')');
        text[keys[count]] = jQuery.trim(cell[keys[count]].text());
    }

    if (text['Location'] === 'Internet' || text['Call Number'] === '') {
        // Remove call numbers from items available on the Internet.
        cell['Call Number'].empty();
    } else {
        // If the item has a call number, append the Map It button.
        cell['Call Number'].prepend(
            '<a href="http://lib.bgsu.edu/catalog/stackmap/search.php?' +
                jQuery.param({
                    'loc_arr': text['Location'],
                    'call_arr': text['Call Number']
                }) +
                '" class="map-it">' +
            '<img src="https://lib.bgsu.edu/catalog/images/btn_mapit.png"' +
            ' alt="Map It"></a>'
        );
    }
});

