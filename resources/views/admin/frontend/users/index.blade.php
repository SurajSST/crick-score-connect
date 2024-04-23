@extends('layouts.admin')

@push('styles')
<link href="{{ asset('backend/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet">
@endpush

@section('body')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">User Details</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example3" class="display min-w850">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Player Type</th>
                                <th>DOB</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loop through users data -->
                            @foreach($users as $key => $user)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td class="player-img">
                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Player Image" style="max-width: 100px; max-height: 100px;">
                                </td>
                                <td class="player-name">{{ $user->name }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->email }}</td>
                                <td class="player-type">{{ $user->playerType }}</td>
                                <td>{{ $user->dob }}</td>
                                <td>{{ $user->phone }}</td>
                                <td>{{ $user->address }}</td>
                                <td>
                                    <div class="d-flex">
                                        <button class="btn btn-info shadow btn-xs sharp btn-view-stats" data-user-id="{{ $user->id }}"><i class="fa fa-eye"></i></button> <a href="{{ route('admin.user.edit', $user->id) }}" class="btn btn-primary shadow btn-xs sharp mr-1"><i class="fa fa-pencil"></i></a>
                                        <!-- <form action="{{ route('admin.user.delete', $user->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger shadow btn-xs sharp" onclick="displayConfirmationDialog('Are you sure you want to delete?', this.form)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form> -->
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid Modal -->
<div class="modal fade" id="userStatsModal" tabindex="-1" aria-labelledby="userStatsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Stats</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
            <img id="player-image" src="" alt="Player Image" style="max-width: 150px; max-height: 150px;">
                <div class="player-info">
                    <h5 style="display: inline-block;">Name: <span id="player-name"></span></h5>
                    <h5 style="display: inline-block; margin-left: 20px;">Player Type: <span id="player-type"></span></h5>
                </div>
                <hr>
                <!-- Batting Stats -->
                <div class="col-md-12">
                    <h5>Batting Stats</h5>
                    <div class="row">
                        <div class="col-md-2">Matches</div>
                        <div class="col-md-2">Runs</div>
                        <div class="col-md-2">Innings</div>
                        <div class="col-md-2">Average</div>
                        <div class="col-md-2">Highest</div>
                        <div class="col-md-2">Strike Rate</div>
                        <div class="col-md-2" id="batting-matches"></div>
                        <div class="col-md-2" id="batting-runs"></div>
                        <div class="col-md-2" id="batting-innings"></div>
                        <div class="col-md-2" id="batting-average"></div>
                        <div class="col-md-2" id="batting-highest"></div>
                        <div class="col-md-2" id="batting-strike-rate"></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-3">Fours:</div>
                        <div class="col-md-3">Sixes:</div>
                        <div class="col-md-3">Fifties:</div>
                        <div class="col-md-3">Hundreds:</div>
                        <div class="col-md-3" id="batting-fours"></div>
                        <div class="col-md-3" id="batting-sixes"></div>
                        <div class="col-md-3" id="batting-fifties"></div>
                        <div class="col-md-3" id="batting-hundreds"></div>
                    </div>
                </div>
                <hr>
                <!-- Bowling Stats -->
                <div class="col-md-12">
                    <h5>Bowling Stats</h5>
                    <div class="row">
                        <div class="col-md-2">Matches</div>
                        <div class="col-md-2">Innings</div>
                        <div class="col-md-2">Runs</div>
                        <div class="col-md-2">Overs</div>
                        <div class="col-md-2">Strike Rate</div>
                        <div class="col-md-2">Maidens</div>

                        <div class="col-md-2" id="bowling-matches"></div>
                        <div class="col-md-2" id="bowling-innings"></div>
                        <div class="col-md-2" id="bowling-runs-conceded"></div>
                        <div class="col-md-2" id="bowling-overs"></div>
                        <div class="col-md-2" id="bowling-strike-rate"></div>
                        <div class="col-md-2" id="bowling-maidens"></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">Wickets</div>
                        <div class="col-md-4">Best Bowling</div>
                        <div class="col-md-4">Economy Rate</div>
                        <div class="col-md-4" id="bowling-wickets"></div>
                        <div class="col-md-4" id="bowling-best-bowling"></div>
                        <div class="col-md-4" id="bowling-economy-rate"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('backend/vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/js/plugins-init/datatables.init.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.btn-view-stats').click(function() {
            var userId = $(this).data('user-id');

            // Extract player name and player type from the table row
            var playerName = $(this).closest('tr').find('.player-name').text();
            var playerType = $(this).closest('tr').find('.player-type').text();
            var playerImageSrc = $(this).closest('tr').find('.player-img img').attr('src');

            // Set player name and player type in the modal
            $('#player-name').text(playerName);
            $('#player-type').text(playerType);
            $('#player-image').attr('src', playerImageSrc);
            $.ajax({
                url: '/api/user/' + userId + '/stats',
                method: 'GET',
                success: function(response) {
                    // Populate batting stats
                    $('#batting-matches').text(response.batting.matches);
                    $('#batting-innings').text(response.batting.innings);
                    $('#batting-runs').text(response.batting.runs);
                    $('#batting-average').text(response.batting.average);
                    $('#batting-highest').text(response.batting.highest);
                    $('#batting-strike-rate').text(response.batting.strikeRate);
                    $('#batting-fours').text(response.batting.fours);
                    $('#batting-sixes').text(response.batting.sixes);
                    $('#batting-fifties').text(response.batting.fifties);
                    $('#batting-hundreds').text(response.batting.hundreds);

                    // Populate bowling stats
                    $('#bowling-matches').text(response.bowling.matches);
                    $('#bowling-innings').text(response.bowling.innings);
                    $('#bowling-runs-conceded').text(response.bowling.runs);
                    $('#bowling-overs').text(response.bowling.overs);
                    $('#bowling-strike-rate').text(response.bowling.strikeRate);
                    $('#bowling-maidens').text(response.bowling.maidens);
                    $('#bowling-wickets').text(response.bowling.wickets);
                    var bestBowling = response.bowling.bBowling;
                    var bestBowlingDisplay = bestBowling.wickets + "/" + bestBowling.runs;
                    $('#bowling-best-bowling').text(bestBowlingDisplay);

                    $('#bowling-economy-rate').text(response.bowling.ecoRate);

                    $('#userStatsModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
@endpush
