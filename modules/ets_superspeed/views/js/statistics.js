/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
var options = {
    series: {
        lines: {
            show: true,
            lineWidth: 1,
            fill: true
        }
    },
    xaxis: {
        mode: "time",
        tickFormatter: function (v, axis) {
            var date = new Date(v);
            if (date.getSeconds() % 20 == 0) {
                var hours = date.getHours() < 10 ? "0" + date.getHours() : date.getHours();
                var minutes = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();
                var seconds = date.getSeconds() < 10 ? "0" + date.getSeconds() : date.getSeconds();
                return hours + ":" + minutes + ":" + seconds;
            } else {
                return "";
            }
        },
        axisLabel: "Time",
        axisLabelUseCanvas: true,
        axisLabelFontSizePixels: 12,
        axisLabelFontFamily: 'Verdana, Arial',
        axisLabelPadding: 10
    },
    yaxis: {
        min: 0,
        tickFormatter: function (v, axis) {
            return Math.round(v*100)/100 + "s";
        }, 
        axisLabel: "Speed loading",
        axisLabelUseCanvas: false,
        axisLabelFontSizePixels: 12,
        axisLabelFontFamily: 'Verdana, Arial',
        axisLabelPadding: 6
    },
    legend: {        
        labelBoxBorderColor: "#fff"
    },
    grid: {                
        backgroundColor: "#fff",
        tickColor: "#ececec",
        borderColor: '#ececec',
    }
};
$(document).ready(function () {
    $(document).ajaxStart(function() {
      $("#ajax_running").remove();
      setTimeout(function(){ $("#ajax_running").hide(); }, 500); 
    });
    dataset = [
        { label: page_loading_time_text, data: dataTimes, color: "#d1e0b9"}
    ];
    $.plot($("#flot-placeholder1"), dataset, options);
    updateStatistic();
});
function updateSystemAnalytics()
{
    var start_time_cache = new Date().getTime();
    $.ajax({
         url: url_home,
         data: '',
         type: "GET",
         beforeSend: function(xhr){xhr.setRequestHeader('XTestSS', 'click');},
         success: function(datajson, status, xhr) {
             if(typeof _ets_sp_parseImageCssHome === typeof Function)
                 _ets_sp_parseImageCssHome(datajson);
            var headers = xhr.getAllResponseHeaders();
            headers = headers.split("\n");
            request_time_cache = new Date().getTime() - start_time_cache;
            if(headers.length)
            {
                for(var i=0;i < headers.length;i++)
                {
                    if(headers[i].toLowerCase().indexOf('x-ss')==0)
                    {
                        var total_sql_cache = ets_sp_getTotalSqls(headers[i]);
                        if(headers[i].toLowerCase().indexOf('cached') >=0)
                        {
                            var start_time_no_cache = new Date().getTime();
                            $.ajax({
                             url: url_home,
                             data: '',
                             type: "POST",
                             beforeSend: function(xhr){xhr.setRequestHeader('XTestSS', 'click');},
                             success: function(datajson, status, xhr) {
                                  var headers = xhr.getAllResponseHeaders();
                                  headers = headers.split("\n");
                                  request_time_no_cache = new Date().getTime() - start_time_no_cache;
                                  if(headers.length)
                                  {
                                        for(var i=0;i < headers.length;i++)
                                        {
                                            if(headers[i].toLowerCase().indexOf('x-ss')==0)
                                            {
                                                $('.gach').hide();
                                                var total_sql_no_cache = ets_sp_getTotalSqls(headers[i]);
                                                $('.table_analytics tr.sql .cached').show();
                                                $('.table_analytics tr.sql .cached .number').html(total_sql_cache);
                                                $('.table_analytics tr.sql .cached .saved').html(total_sql_no_cache-total_sql_cache);
                                                
                                                $('.table_analytics tr.time .cached').show();
                                                $('.table_analytics tr.time .cached .number').html(request_time_cache+'ms');
                                                var time_persent = (request_time_no_cache -request_time_cache)*100/request_time_no_cache
                                                $('.table_analytics tr.time .cached .saved').html( (parseInt(time_persent*100)/100)+'%');
                                                
                                                $('.table_analytics tr.sql .no-cache').show();
                                                $('.table_analytics tr.sql .no-cache .number').html(total_sql_no_cache);
                                                $('.table_analytics tr.time .no-cache').show();
                                                $('.table_analytics tr.time .no-cache .number').html(request_time_no_cache+'ms');
                                            }
                                        }
                                  }
                             },
                             error: function(){
                             }
                         });
                        }
                        else
                        {
                            $('.gach').hide();
                            $('.table_analytics tr.sql .no-cache').show();
                            $('.table_analytics tr.sql .no-cache .number').html(total_sql_cache);
                            $('.table_analytics tr.time .no-cache').show();
                            $('.table_analytics tr.time .no-cache .number').html(request_time_cache);
                        }
                    }
                }
            }  
         },
         error: function(){
            
         }
     });
}
function ets_sp_getTotalSqls(text)
{
    var cache_val = text.split(',');
    if(cache_val.length==2)
    {
        var sqls = cache_val[1].split('/');
        if(sqls.length==2)
        {
            return sqls[1];               
        }
    }
    return 0;
}
function updateStatistic() { 
     var start_time = new Date().getTime();
     $.ajax({
         url: url_home,
         data: '',
         type: "GET",
         beforeSend: function(xhr){xhr.setRequestHeader('XTestSS', 'click');},
         success: function() {
            request_time = new Date().getTime() - start_time;
            if(request_time)
            $.ajax({
    			type: 'POST',
    			headers: { "cache-control": "no-cache" },
    			url: '',
    			async: true,
    			cache: true,
    			dataType : "json",
                data:'&getTimeSpeed=1&request_time='+request_time,
    			success: function(json)
    			{
    			     $('.error_load').remove();
    			     if(json.time) 
                     {
                        var temp_date= new Date(json.time).getTime();
                        var temp = [temp_date, json.value];
                        dataTimes.shift();
                        dataTimes.push(temp);
                        updateSpeedMeter(json.value);
                     }
                     dataset = [
                        { label: page_loading_time_text, data: dataTimes, color: "#d1e0b9" }
                     ];
                     $.plot($("#flot-placeholder1"), dataset, options);
                     setTimeout(updateStatistic, updateInterval);
                     $( "#ajax_running" ).removeClass('sp_hide');
    			},
                error: function(xhr, status, error)
                {
                    setTimeout(updateStatistic, updateInterval);         
                }
            });
            else
                setTimeout(updateStatistic, updateInterval);  
         },
         error: function(){
            setTimeout(updateStatistic, updateInterval);
         }
     });
}