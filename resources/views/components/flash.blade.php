<div class="site-width" style="padding-top:12px">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-error"><strong>Vui lòng kiểm tra lại thông tin:</strong><ul style="margin:6px 0 0 18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
</div>
