<script>
    // Polr Server selection button
    polr.home.server = {!! pr()->select('select-server') !!};
    $('#btn-change-server').click(function(){
        polr.home.server = {!! pr()->select('select-server') !!};
    });
    polr.home.init();
    polr.stats.leftBound = '{{ $datePickerLeftBound }}';
    polr.stats.rightBound = '{{ $datePickerRightBound }}';
    polr.stats.initDatePickers();
    polr.home.setHandlers();
</script>
