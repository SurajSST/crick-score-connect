<div class="deznav">
    <div class="deznav-scroll">
        <ul class="metismenu" id="menu">
            <li>
                <a href="{{ route('admin.index') }}" class="ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fa fa-tachometer"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fa fa-futbol-o"></i>
                    <span class="nav-text">Sports</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.sports.index') }}">All Sports</a></li>
                    <li><a href="{{ route('admin.sports.create') }}">Add Sports</a></li>
                </ul>
            </li>
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fa fa-users"></i>
                    <span class="nav-text">Teams</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.teams.index') }}">All Teams</a></li>
                    <li><a href="{{ route('admin.teams.create') }}">Add Teams</a></li>
                </ul>
            </li>
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fa fa-user"></i>
                    <span class="nav-text">Players</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.players.index') }}">All Players</a></li>
                    <li><a href="{{ route('admin.players.create') }}">Add Players</a></li>
                </ul>
            </li>
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fa fa-calendar"></i>
                    <span class="nav-text">Matches</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.matches.index') }}">All Matches</a></li>
                    <li><a href="{{ route('admin.matches.create') }}">Add Matches</a></li>
                </ul>
            </li>
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fa fa-bar-chart"></i>
                    <span class="nav-text">Scores</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.scores.index') }}">All Scores</a></li>
                    <li><a href="{{ route('admin.scores.create') }}">Add Scores</a></li>
                </ul>
            </li>
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fa fa-television"></i>
                    <span class="nav-text">Live Matches</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.live-match.index') }}">All Live Matches</a></li>
                    <li><a href="{{ route('admin.live-match.create') }}">Add Live Match</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ route('admin.live-match-comment.index') }}" class="ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fa fa-comments"></i>
                    <span class="nav-text">Live Matches Comment</span>
                </a>
            </li>
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fa fa-newspaper-o"></i>
                    <span class="nav-text">Blogs</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.blogs.index') }}">All Blogs</a></li>
                    <li><a href="{{ route('admin.blogs.create') }}">Add Blog</a></li>
                </ul>
            </li>
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fa fa-star"></i>
                    <span class="nav-text">Highlights</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.highlights.index') }}">All Highlights</a></li>
                    <li><a href="{{ route('admin.highlights.create') }}">Add Highlight</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ route('admin.reports') }}" class="ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fa fa-exclamation-triangle"></i>
                    <span class="nav-text">Reports</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users') }}" class="ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fa fa-users"></i>
                    <span class="nav-text">Users</span>
                </a>
            </li>
        </ul>

        <div class="copyright">
            <p><strong>Olympic Games Admin Dashboard</strong> © 2024 All Rights Reserved</p>
            <p>Made with ♥ by Soniya</p>
        </div>
    </div>
</div>
