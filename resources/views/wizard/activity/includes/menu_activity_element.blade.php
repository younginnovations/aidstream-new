<div class="panel panel-default">
    <div class="panel-heading">Wizard Menu</div>
    <div class="panel-body">
        <ul class="nav">
            <li><a href="{{ route('wizard.activity.create')}}">Step 1</a></li>
            @if(isset($id))
                <li><a href="{{ route('wizard.activity.title-description.index', $id)}}">Step 2</a></li>
                <li><a href="{{ route('wizard.activity.date-status.index', $id)}}">Step 3</a></li>
            @else
                <li><a href="{{ route('wizard.activity.create')}}">Step 2</a></li>
                <li><a href="{{ route('wizard.activity.create')}}">Step 3</a></li>
            @endif
        </ul>
    </div>
</div>
