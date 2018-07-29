if (!Mautic.eAnalytics) {
    (function (w, d, s, g, js, fjs) {
        g = w.gapi || (w.gapi = {});
        g.analytics = {q: [], ready: function (cb) {this.q.push(cb)}};
        js = d.createElement(s);
        fjs = d.getElementsByTagName(s)[0];
        js.src = 'https://apis.google.com/js/platform.js';
        fjs.parentNode.insertBefore(js, fjs);
        js.onload = function () {g.load('analytics')};
    }(window, document, 'script'));
    Mautic.eAnalytics = gapi.analytics;
}

Mautic.eAnalytics.ready(function () {
    mQuery('.analytics-choose select').change(function(){
        getData();
    })

    Mautic.eAnalytics.auth.authorize({
        container: 'auth-button',
        clientid: CLIENT_ID,
    });

    if (Mautic.eAnalytics.auth.isAuthorized()) {
        getData();
    }
    else {
        Mautic.eAnalytics.auth.on('success', function (response) {
            getData();
        }).on('logout', function (response) {
        }).on('needsAuthorization', function (response) {
            document.getElementById("analytics-loading").style.display = 'none';
            document.getElementById("analytics-auth").style.display = 'block';
        }).on('error', function (response) {
            console.log('error');
        })
    }
});

function getData () {
    document.getElementById("analytics-loading").style.display = 'none';

    var selectedFilters = [];
    mQuery('.analytics-choose select').each(function(){
        var opts = mQuery(this).val();
        var key = mQuery(this).attr('name');
        var filters = [];
        if(opts) {
            opts.forEach(function (entry) {
                filters.push('ga:' + key + '==' + entry);
            });
            selectedFilters.push(filters.join(','));
        }

    })
    filters = selectedFilters.join(';');
    console.log(filters);

    var dataChart = new gapi.analytics.googleCharts.DataChart({
        query: {
            'ids': ids,
            metrics: metricsGraph,
            dimensions: 'ga:date',
            'start-date': dateFrom,
            'end-date': dateTo,
            'filters': filters
        },
        chart: {
            container: 'chart-container',
            type: 'LINE',
            options: {
                width: '100%',
                height:'100px'
            }
        }
    })
    dataChart.execute();

    query({
        'ids': ids,
        'dimensions': 'ga:sourceMedium',
        'metrics': metrics,
        'start-date': dateFrom,
        'end-date': dateTo,
        'filters': filters
    })
        .then(function (response) {
            if (response.totalResults > 0) {
                var results = response.totalsForAllResults;
                var symbols = [];
                response.columnHeaders.forEach(function (row, i) {
                    var symbol = '';
                    switch (row['dataType']) {
                        case "PERCENT":
                            symbol = '%';
                            break;
                        case "CURRENCY":
                            symbol = currency;
                            break;
                        case "TIME":
                            symbol = 'm';
                            results[row['name']] = fmtMSS((parseInt(results[row['name']])));
                            //console.log(results[row['name']]);
                            break;

                    }
                    symbols[row['name']] = symbol;
                });
                for (var key in results) {
                    var result = results[key];
                    if (result == parseFloat(result) && result != parseInt(result)) {
                        result = parseFloat(result).toFixed(1);
                    }
                    if (result && document.getElementById(key) != null) {
                        document.getElementById(key).innerHTML = result + '' + symbols[key];
                    }
                }
                document.getElementById("eanalytics-stats").style.display = 'block';
            }
            else {
                document.getElementById("eanalytics-stats-no-results").style.display = 'block';

            }
        });
}

function fmtMSS(s){return(s-(s%=60))/60+(9<s?':':':0')+s}

/**
 * Extend the Embed APIs `Mautic.eAnalytics.report.Data` component to
 * return a promise the is fulfilled with the value returned by the API.
 * @param {Object} params The request parameters.
 * @return {Promise} A promise.
 */
function query (params) {
    return new Promise(function (resolve, reject) {
        var data = new Mautic.eAnalytics.report.Data({query: params});
        data.once('success', function (response) { resolve(response); })
            .once('error', function (response) { reject(response); })
            .execute();
    });
}