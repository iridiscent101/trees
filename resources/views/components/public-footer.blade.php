<footer class="footer border-t border-base-300 bg-neutral p-6 text-neutral-content sm:footer-horizontal" aria-label="NVSU partners and contact information">
    <aside class="max-w-xs">
        <img class="h-14 w-14 rounded-full bg-white object-contain p-1" src="{{ asset('images/nvsu.gif') }}" alt="Nueva Vizcaya State University logo">
        <p><strong class="text-base">Nueva Vizcaya State University</strong><br>Bayombong, Nueva Vizcaya, Philippines</p>
    </aside>
    <nav>
        <h2 class="footer-title">Project Partners</h2>
        <span>College of Information Technology Education</span>
        <span>College of Forestry, Environment and Resources Management</span>
        <span>QR Code-Integrated Tree Monitoring System</span>
    </nav>
    <nav>
        <h2 class="footer-title">Explore</h2>
        <a class="link link-hover" href="{{ route('trees.index') }}">Tree Directory</a>
        <a class="link link-hover" href="{{ route('campus-map') }}">Campus Map</a>
        <a class="link link-hover" href="{{ route('about') }}">About the Project</a>
    </nav>
    <address class="not-italic">
        <h2 class="footer-title">Contact</h2>
        <a class="link link-hover block" href="mailto:cited@nvsu.edu.ph">cited@nvsu.edu.ph</a>
        <span class="block">(078) 392-1111 Local 212</span>
        <a class="link link-hover block" href="https://www.nvsu.edu.ph">www.nvsu.edu.ph</a>
    </address>
</footer>
