{{-- CMS Sidebar – nutzt zentrale Sidebar-Renderer mit Config --}}
<div>
    <x-ui-sidebar :items="config('cms.sidebar')" />
</div>