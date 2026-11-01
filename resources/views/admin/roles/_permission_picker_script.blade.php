<script>
$(function () {
    var $select = $('#permissions');
    var $picker = $('#permission-picker');

    // Keep the dropdown open while picking several permissions.
    if ($select.hasClass('select2-hidden-accessible')) { $select.select2('destroy'); }
    $select.select2({ closeOnSelect: false });

    function selectedIds() { return ($select.val() || []).map(String); }

    function syncPicker() {
        var ids = selectedIds();
        $picker.find('.perm-box').each(function () {
            $(this).prop('checked', ids.indexOf(String($(this).data('id'))) !== -1);
        });
        $picker.find('tr[data-group]').each(function () {
            var $boxes = $(this).find('.perm-box');
            var checked = $boxes.filter(':checked').length;
            $(this).find('.group-toggle')
                .prop('checked', checked === $boxes.length)
                .prop('indeterminate', checked > 0 && checked < $boxes.length);
        });
    }

    function setIds(ids) {
        $select.val(ids.map(String)).trigger('change');
    }

    $picker.on('change', '.perm-box', function () {
        var ids = selectedIds(), id = String($(this).data('id')), at = ids.indexOf(id);
        if (this.checked && at === -1) { ids.push(id); }
        if (!this.checked && at !== -1) { ids.splice(at, 1); }
        setIds(ids);
    });

    $picker.on('change', '.group-toggle', function () {
        var on = this.checked, ids = selectedIds();
        $(this).closest('tr').find('.perm-box').each(function () {
            var id = String($(this).data('id')), at = ids.indexOf(id);
            if (on && at === -1) { ids.push(id); }
            if (!on && at !== -1) { ids.splice(at, 1); }
        });
        setIds(ids);
    });

    $picker.on('click', '[data-pick]', function () {
        var mode = $(this).data('pick');
        if (mode === 'all') {
            setIds($select.find('option').map(function () { return this.value; }).get());
        } else if (mode === 'none') {
            setIds([]);
        } else {
            setIds($picker.find('.perm-box').filter(function () {
                var a = $(this).data('action');
                return a === 'access' || a === 'show';
            }).map(function () { return $(this).data('id'); }).get());
        }
    });

    // Anything that changes the dropdown (typing, the old Select all / Deselect all) updates the boxes.
    $select.on('change', syncPicker);
    syncPicker();
});
</script>
