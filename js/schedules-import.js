jQuery(document).ready(function($){

    function getMonday(d) {
        d = new Date(d);
        var day = d.getDay(),
            diff = d.getDate() - day + (day == 0 ? -6:1); // adjust when day is sunday
        return new Date(d.setDate(diff));
    }

    $.ajax({
        method: 'post',
        url: '/wp-json/radioking/schedules-import',
        data: {
            radioking_access_token: radioking_access_token,
        }
    }).done(function (schedules) {
        var monday = getMonday(new Date());

        schedules.forEach(function(schedule){
            if(schedule.day_playlist) return;
            var schedule_start = new Date(schedule.schedule_start);
            var schedule_end = new Date(schedule.schedule_end);
            if(schedule_start < monday ) return;
            var schedule_start_day = schedule_start.getDay(),
                schedule_start_time_percent = schedule_start.getHours()/24 + schedule_start.getMinutes()/(24*60),
                schedule_end_time_percent = schedule_end.getHours()/24 + schedule_end.getMinutes()/(24*60),
                schedule_duration_time_percent = schedule_end_time_percent - schedule_start_time_percent,
                top = (schedule_start_time_percent*100).toFixed(2)+'%',
                height = (schedule_duration_time_percent*100).toFixed(2)+'%';
            var scheduleHtml = '<div class="planning__schedule" style="top:'+top+';height:'+height+'">'+schedule.name+'</div>';
            $('.planning__day.day-'+schedule_start_day).append(scheduleHtml);
            console.log(schedule.name, schedule_start_time_percent, schedule_end_time_percent, schedule);
        })
    }).fail(function(){

    });
})