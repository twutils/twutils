<!-- Header with Background Image -->
    <header class="pt-4 welcomeHeader">
      <div class="container-fluid">
        <div class="row align-items-center text-center h-100 pt-5">
          <div class="col-sm-8">
            <h5 class="text-center mt-4">
                <img style="max-width: 400px;" class="img-fluid w-100" src="{{url('images/twutlis-typo.png')}}">
                <div class="mt-2 welcomeHeader-text">
                  {{__('messages.brand_desc')}}
                </div>
            </h5>
          </div>
          <div class="col-sm-4 d-none d-sm-block">
            <img style="max-width: 350px;" class="img-fluid w-100 animated bounceInDown logo" src="{{url('images/twutils3.png')}}">
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12 text-center">
            <a class="btn btn-lg mainActionButton" href="{{route('app')}}">{{__('messages.goto_home')}}</a>
          </div>
        </div>
      </div>
    </header>