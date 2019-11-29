jQuery(document).ready(function($){
    var buttonStartTracksImport = $('.start-tracks-import');
    var progress = $('.tracks-import-progress');
    var trackImportList = $('.tracks-import-list');
    var trackBoxesSelect = $('.track-boxes-select');
    var idtrackbox = 1;

    buttonStartTracksImport.click(function(){
        buttonStartTracksImport.attr('disabled',true);
        StartTrackImport();
    })

    trackBoxesSelect.change(function(e){
        var id = trackBoxesSelect.find(':selected').val(),
            trackbox = track_boxes.find(function(trackbox){
           return trackbox.idtrackbox === id;
        });
        idtrackbox = trackbox.idtrackbox;
        $('.trackbox-count').text(trackbox.count);
        progress.text(trackbox.count);
    });
    function StartTrackImport(){
        var total_done = 0, total_todo = progress.attr('max'), startTime = Date.now();
        function TrackImport(offset) {
            $.ajax({
                method: 'post',
                url: '/wp-json/radioking/tracks-import',
                data: {
                    radioking_access_token: radioking_access_token,
                    idtrackbox: idtrackbox,
                    offset: offset,
                    limit: 10,
                }
            }).done(function (tracks) {
                total_done += tracks.length;
                var ratio_done = total_done/total_todo,
                    time_done = Date.now()-startTime,
                    time_estimed = (time_done*total_todo/total_done)-time_done;
                $('.import-progress-label').html('<code>'+(100*ratio_done).toFixed(2)+'%</code>- temps écoulé : '+(time_done/1000).toFixed()+'s  — temps restant estimé : '+(time_estimed/1000).toFixed(0)+'s');
                var output = '';
                tracks.forEach(function(t){
                    output+=
                        '<div class="track">' +
                        '<small><code class="wp_track">'+t.wp_track.ID+'</code></small> ' +
                        '<span class="title">'+t.track.title+'</span> ' +
                        '<span class="album">'+t.track.album+'</span> ' +
                        '<span class="artist">'+t.track.artist+'</span>' +
                        '</div>'
                    ;
                });
                trackImportList.prepend(output);
                if(tracks.length){
                    progress.attr('value',total_done);
                    TrackImport(total_done);
                }else{
                    buttonStartTracksImport.attr('disabled',false);
                }
            });
        }
        TrackImport(0);
    }

})