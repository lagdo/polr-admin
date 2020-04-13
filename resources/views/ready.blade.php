<script>
    // Polr Server selection button
    $('#btn-change-server').click(function(){
        {!! $home->reload(pr()->select('select-server')) !!};
    });
    polr.init('{{ $datePickerLeftBound }}', '{{ $datePickerRightBound }}');
</script>
