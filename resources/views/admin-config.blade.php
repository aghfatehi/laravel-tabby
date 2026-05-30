<form class="form-horizontal" action="{{ $updateRoute ?? '#' }}" method="POST">
    @csrf
    <input type="hidden" name="payment_method" value="tabby">

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-form-label">{{ __('Sandbox Mode') }}</label>
        </div>
        <div class="col-md-8">
            <label class="switch">
                <input type="checkbox" name="TABBY_SANDBOX_MODE"
                    @if(config('tabby.sandbox')) checked @endif>
                <span class="slider round"></span>
            </label>
            <span class="text-muted fs-12">{{ __('Enable for testing') }}</span>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-form-label">{{ __('Secret Key') }}</label>
        </div>
        <div class="col-md-8">
            <input type="password" class="form-control" name="TABBY_SECRET_KEY"
                value="{{ config('tabby.secret_key') }}"
                placeholder="{{ __('Enter Tabby Secret Key') }}" required>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-form-label">{{ __('Merchant Code') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="TABBY_MERCHANT_CODE"
                value="{{ config('tabby.merchant_code') }}"
                placeholder="{{ __('e.g. ae, sa, kw') }}" required>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-form-label">{{ __('Region') }}</label>
        </div>
        <div class="col-md-8">
            <select class="form-control" name="TABBY_REGION">
                @foreach(['sa' => 'Saudi Arabia', 'ae' => 'United Arab Emirates', 'kw' => 'Kuwait'] as $code => $name)
                    <option value="{{ $code }}" @if(config('tabby.region') === $code) selected @endif>
                        {{ __($name) }} ({{ $code }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-form-label">{{ __('Currency') }}</label>
        </div>
        <div class="col-md-8">
            <select class="form-control" name="TABBY_CURRENCY">
                @foreach(['SAR' => 'Saudi Riyal', 'AED' => 'UAE Dirham', 'KWD' => 'Kuwaiti Dinar'] as $code => $name)
                    <option value="{{ $code }}" @if(config('tabby.currency') === $code) selected @endif>
                        {{ __($name) }} ({{ $code }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-form-label">{{ __('Language') }}</label>
        </div>
        <div class="col-md-8">
            <select class="form-control" name="TABBY_LANGUAGE">
                <option value="en" @if(config('tabby.language') === 'en') selected @endif>{{ __('English') }}</option>
                <option value="ar" @if(config('tabby.language') === 'ar') selected @endif>{{ __('Arabic') }}</option>
            </select>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-form-label">{{ __('Enable Logging') }}</label>
        </div>
        <div class="col-md-8">
            <label class="switch">
                <input type="checkbox" name="TABBY_LOGGING"
                    @if(config('tabby.logging')) checked @endif>
                <span class="slider round"></span>
            </label>
            <span class="text-muted fs-12">{{ __('Log API requests and responses') }}</span>
        </div>
    </div>

    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
    </div>
</form>
