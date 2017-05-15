@extends('layouts.app')

@section('content')
	<div class="container">
		<div>
			@if($volume->total != 0)
				<div class="row text-center pt-4">
					<h3 class="mx-auto">
						<b>{{ $volume->total }}</b> articles were analyzed from <b>{{ $volume->sources }}</b>
						sources.<br><b>{{ $volume->relevant }} ({{ floor($volume->relevant * 100 / $volume->total) }}
							%)</b>
						articles mentioned one or more countries.
					</h3>
				</div>
				<h1 class="text-center">
					The world is gazing upon
					<b>
						{{
							in_array(key($latest), ['CZ', 'CK', 'CF', 'BS', 'CD', 'DO', 'VA', 'MH', 'NL', 'SC', 'SB', 'AE', 'GB', 'US']) ? 'the' : ''
						}}
						{{
							is_array($countries[key($latest)]['name']['EN']) ?
							$countries[key($latest)]['name']['EN'][0] :
							$countries[key($latest)]['name']['EN']
						}}
					</b>
				</h1>
			@else
				<div class="row text-center pt-4">
					<h1 class="mx-auto">There were no news yesterday! What happened!?!</h1>
				</div>
			@endif
		</div>
		<div class="text-center mx-auto">
			<div id="mostMentionedCountryContainer" style="height: 20rem"></div>
		</div>
		<hr class="pt-4">
		<div class="my-3 text-center">
			This list compiles the data from Alexa's Top News sites, through their "World" RSS feeds.
			<br>
			Click on a country to see how its coverage has changed over time.
		</div>

		<table class="table mx-auto table-hover my-4">
			<thead>
			<tr>
				<th class="text-center">Trend</th>
				<th class="text-center">Country</th>
				<th class="text-center">Mentions</th>
			</tr>
			</thead>
			<tbody>
			@foreach($latest as $countryCode => $amount)
				<tr id="{{ $countryCode }}Row">
					@if($lastTwoDays[$countryCode][0] > $lastTwoDays[$countryCode][1])
						<td class="text-center align-middle green">
							&#9650;
						</td>
					@elseif($lastTwoDays[$countryCode][0] < $lastTwoDays[$countryCode][1])
						<td class="text-center align-middle red">
							&#9660;
						</td>
					@else
						<td class="text-center align-middle blue">
							&#9635;
						</td>
					@endif

					<td class="text-center">{{ is_array($countries[$countryCode]['name']['EN']) ?
					$countries[$countryCode]['name']['EN'][0] :
					$countries[$countryCode]['name']['EN'] }}</td>
					<td class="text-center">{{ $amount }}</td>
				</tr>
				<script>
                    $('#{{ $countryCode }}Row').click(function () {
                        displayCountryModal('{{  $countryCode }}');
                        $('#countryModal').modal();
                    });
				</script>
			@endforeach
			</tbody>
		</table>
	</div>
	<script>
        // Get country data so that it's ready when a user clicks on the modal.
        $(document).ready(function () {
            @foreach($latest as $country => $amount)
				getData('{{ $country }}', 150);
			@endforeach
        });
	</script>
@endsection

@section('after_scripts')
	<script>
        // Set up the "Most mentioned" chart.
        var mostMentioned = JSON.parse('{!! json_encode($mostMentioned) !!}');

        var mostMentionedArrayData = [];
        for (var day in mostMentioned) {
            mostMentionedArrayData.push([new Date(day).getTime(), mostMentioned[day]]);
        }

        mostMentionedArrayData.sort(function (a, b) {
            return b[0] - a[0];
        });

        Highcharts.chart('mostMentionedCountryContainer', {
            chart: {
                type: 'line',
                zoomType: 'x'
            },
            plotOptions: {
                scatter: {
                    lineWidth: 1
                }
            },
            title: {
                text: ''
            },
            xAxis: {
                type: 'datetime',
                labelFormat: {
                    value: '%d %m'
                }
            },
            yAxis: {
                title: {
                    text: 'Mentions'
                },
                allowDecimals: false
            },
            legend: {
                enabled: false
            },
            series: [{
                name: '{{
					is_array($countries[key($latest)]['name']['EN']) ?
					$countries[key($latest)]['name']['EN'][0] :
					$countries[key($latest)]['name']['EN']
				}}',
                data: mostMentionedArrayData.slice(0, 30) // We only want the last month of coverage.
            }]
        });
	</script>
@endsection