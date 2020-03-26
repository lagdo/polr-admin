<script>
    // Polr Server selection button
    $('#btn-change-server').click(function(){
        polr.init('{{ $datePickerLeftBound }}', '{{ $datePickerRightBound }}');
    });
    polr.init('{{ $datePickerLeftBound }}', '{{ $datePickerRightBound }}');
</script>
