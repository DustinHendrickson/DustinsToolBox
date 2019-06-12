<body>

<div class="dash">
    <div class="dash-nav dash-nav-dark">


        <header>
            <a href="#!" class="menu-toggle">
                <i class="fas fa-bars"></i>
            </a>
            <a href="{!! url('/'); !!}" class="spur-logo"><i class="fas fa-toolbox"></i> <span>Dustin's Tool Box</span></a>
        </header>

        <nav class="dash-nav-list">
            <a href="{!! url('/'); !!}" class="dash-nav-item">
                <i class="fas fa-home"></i>
                Dashboard
            </a>

            <div class="dash-nav-dropdown">
                <a href="#!" class="dash-nav-item dash-nav-dropdown-toggle">
                    <i class="fas fa-hammer"></i>
                    Tools
                </a>

                <div class="dash-nav-dropdown-menu">
                    <a href="{!! url('/banner-tool'); !!}" class="dash-nav-dropdown-item"> Banner Tool</a></i>
                </div>
            </div>

        </nav>


    </div>

    <div class="dash-app">
        <header class="dash-toolbar">
            <a href="#!" class="menu-toggle">
                <i class="fas fa-bars"></i>
            </a>

            <h2>Dustin's Tool Box <i class="fas fa-toolbox"></i></h2>

            <div class="tools">
                <a href="http://github.com/hackerthemes" target="_blank" class="tools-item">
                    <i class="fab fa-github"></i>
                </a>
            </div>

        </header>

        <!-- MAIN CONTENT -->
        @yield('content')

    </div>
</div>
