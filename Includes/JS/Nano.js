
	/**
	 * T+ NANO
	 * A mini library for quick and basic app setup in JavaScript
	 *
	 * Provided functionality such as absolute URLs, AJAX calls and context-awareness
	 *
	 * Please review full documentation at:
	 * https://github.com/tplus-dev/nano
	 *
	 * @version 1.0.0
	 */

	if ( typeof tplus === 'undefined' ) {
		var tplus = {};
	}

	tplus.nano = {};

	/**
	 * T+ Nano App class
	 */
	
	tplus.nano.app = function ( ) {

		/**
		 * Container of classes that are only supposed to be instantiated once
		 * @type {Object}
		 */
		
		this.container = {};

		/**
		 * Return an instance of the environment object
		 *
		 * @return {Object}
		 */
		
		this.environment = function ( ) {
			return this.getFromContainer('environment');
		}

		/**
		 * Return an instance of the options object
		 *
		 * @return {Object}
		 */
		
		this.options = function ( ) {
			return this.getFromContainer('options');
		}

		/**
		 * Helper method, returning an instance of a class, and creates it if this is
		 * first occurence
		 *
		 * This method is not intended for use outside this class scope
		 *
		 * @param {String} library Case-sensitive name of library
		 * @return {Object}
		 */
		
		this.getFromContainer = function ( library ) {
			if ( typeof this.container[library] === 'undefined' ) {
				this.container[library] = new tplus.nano[library];
			}
			return this.container[library];
		}

		/**
		 * Create a new server request instance with environment already injected
		 *
		 * @return {Object} Instance of serverRequest
		 */
		
		this.serverRequest = function ( ) {
			return new tplus.nano.serverRequest(
				this.getFromContainer('environment'),
				this.getFromContainer('options')
			);
		}

	}

	/**
	 * T+ Nano Options class
	 */
	
	tplus.nano.options = function ( ) {

		/**
		 * Container of provided options
		 * @type {Object}
		 */
		
		this.options = {};

		/**
		 * Set a property
		 * @param {String} key
		 * @param {mixed} value
		 * @return {Object} options
		 */
		
		this.set = function ( key, value ) {

			this.options[key] = value;
			return this;

		}

		/**
		 * Retrieve an option
		 * If option isn't set, we'll return false
		 *
		 * @param {String} key
		 * @return {mixed}
		 */

		this.get = function ( key ) {

			// If value isn't configured, we'll return false instead of undefined
			if ( typeof this.options[key] === 'undefined' ) {
				return false;
			}

			return this.options[key];

		}

	}

	/**
	 * T+ Nano Environment class
	 */
	
	tplus.nano.environment = function ( ) {

		this.development = 0x1;
		this.production = 0x2;

		/**
		 * ID of the environment (ie. production or dev.)
		 * @type {Number|undefined}
		 */
		
		this.envId;

		/**
		 * Optional (alternative) URL method
		 * @type {Function|undefined}
		 */
		
		this.urlMethod;

		/**
		 * Inject a custom function to defining the URL
		 * Especially useful when domains require additional URI, such as localhost,
		 * for example: http://localhost/my-project/
		 *
		 * @param {Function} closure
		 * @return {Object} Instance of environment
		 */

		this.defineUrl = function ( closure ) {

			// Ensure closure parameter is a function
			if ( typeof closure !== 'function' ) {
				console.error( 'Closure passed in defineUrl must be a function' );
			}

			this.urlMethod = closure;
			return this;

		}

		/**
		 * Retrieve the URL dynamically
		 *
		 * @param {String} uri Optional URI
		 * @return {String}
		 */

		this.url = function ( uri ) {

			// If uri is undefined, or anything else than a string, we'll set it to
			// an empty string, to avoid errors when performing regular expressions
			// on it
			if ( typeof uri !== 'string' ) {
				uri = '';
			}

			// Remove prepending slashes
			uri = uri.replace( /^\//, '' );

			// If the custom URL method is defined, we'll return its output
			if ( typeof this.urlMethod === 'function' ) {
				return this.urlMethod( uri );
			} else {

				// Otherwise, by default, we'll return the protocol and hostname,
				// and append the URI
				return location.protocol + '//' + location.hostname + '/' + uri;

			}

		}

		/**
		 * Configure the environment as development when hostname matches one of the array
		 * elements
		 *
		 * @param {Array} developmentDomains List of domains that are used for development
		 * @return {Object} Instance of self
		 */
		
		this.developmentWhen = function ( developmentDomains ) {

			// Set Environment ID to development, if the domain is found in the provided list
			if ( developmentDomains.indexOf( this.getHostname() ) > -1 ) {
				this.envId = this.development;
			}

			return this;

		}

		/**
		 * Configure the environment as production when hostname matches one of the array
		 * elements
		 *
		 * @param {Array} productionDomains List of domains that are used for production
		 * @return {Object} Instance of self
		 */

		this.productionWhen = function ( productionDomains ) {

			// Set Enviroment ID to production, if the domain is found in the provided list
			if ( productionDomains.indexOf( this.getHostname() ) > -1 ) {
				this.envId = this.production;
			}

			return this;

		}

		/**
		 * Wrapper/helper method for retrieving the hostname
		 * Primarily added, in case we sometime in the future might need to work with
		 * ports, alternate protocols or something else entirely
		 * 
		 * @return {String}
		 */
		
		this.getHostname = function ( ) {
			return location.hostname;
		}

		/**
		 * Wrapper/helper method for retrieving the environment ID
		 * Ensures the ID is defined
		 *
		 * @return {Number}
		 */

		this.getEnvId = function ( ) {
			if ( typeof this.envId != 'number' ) {
				console.error( 'Environment has not been defined' );
			}
			return this.envId;
		}

		/**
		 * Returns true if we are operating in a development environment
		 *
		 * @return {Boolean}
		 */

		this.isDevelopment = function ( ) {
			return this.getEnvId() == this.development;
		}

		/**
		 * Returns true if we are operating in production environment
		 *
		 * @return {Boolean}
		 */

		this.isProduction = function ( ) {
			return this.getEnvId() == this.production;
		}

	}

	/**
	 * T+ Nano Server Request class
	 * Performs REST-based calls in JSON format to specified server URL(s)
	 *
	 * @param {Object} environment Instance of the T+ Nano Environment class
	 * @param {Object} options Instnace of the T+ Nano Options class
	 */
	
	tplus.nano.serverRequest = function ( environment, options ) {

		/**
		 * Container for options object
		 * @type {Object}
		 */
		
		this.options = options;

		/**
		 * Container for environment object
		 * @type {Object}
		 */
		
		this.environment = environment;

		/**
		 * Default configuration for request method and parameters
		 * @type {Object}
		 */
		
		this.configuration = {
			requestMethod: 'GET',
			parameters: {}
		};

		/**
		 * Container for default callbacks - imported from the passed options
		 * if available
		 * @type {Object}
		 */

		this.callbacks = {
			success: options.get('serverRequest.onSuccess'),
			error: options.get('serverRequest.onError'),
			always: options.get('serverRequest.always')
		}

		/**
		 * Set the parameters/data to be passed for the AJAX call
		 *
		 * @param {Object} parameters
		 * @return {Object} serverRequest
		 */

		this.parameters = function ( parameters ) {
			this.configuration.parameters = parameters;
			return this;
		}

		/**
		 * Get the list of supported REST request types
		 *
		 * @return {Array}
		 */

		this.getRequestMethods = function ( ) {
			return [ 'GET', 'POST', 'PATCH', 'PUT', 'DELETE' ];
		}

		/**
		 * Set the request method (for instance GET or PUT)
		 * The method must be available in the getRequestMethods list
		 *
		 * @param {String} requestMethod
		 * @return {Object} serverRequest
		 */

		this.requestMethod = function ( requestMethod ) {

			// Verify that the provided method is allowed per the getRequestMethods method
			if ( this.getRequestMethods().indexOf( requestMethod ) == -1 ) {
				console.error( 'Request method now allowed: ' + requestMethod );
			}

			this.configuration.requestMethod = requestMethod;
			return this;

		}

		/**
		 * Retrieve a provided callback, or return an empty function
		 *
		 * @param {String} callbackName Name of the callback
		 * @return {Function}
		 */

		this.getCallback = function ( callbackName ) {

			if ( typeof this.callbacks[callbackName] !== 'function' ) {
				return function (){};
			}

			return this.callbacks[callbackName];

		}

		/**
		 * Helper method for streamlining the process of setting callbacks
		 *
		 * @param {String} callbackName
		 * @param {Function} closure
		 * @return {void}
		 */
		
		this.setCallback = function ( callbackName, closure ) {

			if ( typeof closure !== 'function' ) {
				console.error( 'Parameter passed for callback is not a function' );
			}

			this.callbacks[callbackName] = closure;

			return this;

		}

		/**
		 * Set the "onSuccess" callback
		 *
		 * @param {Function} closure
		 * @return {Object} serverRequest
		 */
		
		this.onSuccess = function ( closure ) {
			return this.setCallback( 'success', closure );
		}

		/**
		 * Set the "onError" callback
		 *
		 * @param {Function} closure
		 * @return {Object} serverRequest
		 */
		
		this.onError = function ( closure ) {
			return this.setCallback( 'error', closure );
		}

		/**
		 * Set the "always" callback
		 *
		 * @param {Function} closure
		 * @return {Object} serverRequest
		 */
		
		this.always = function ( closure ) {
			return this.setCallback( 'always', closure );
		}

		/**
		 * Execute the server request on the provided URI
		 *
		 * @param {String} uri
		 * @return {void}
		 */

		this.execute = function ( uri ) {

			// We currently require jQuery for carrying out the AJAX call
			if ( typeof jQuery === 'undefined' ) {
				console.error( 'jQuery is required to run AJAX calls' );
			}

			// Set all callbacks on variables for access in the callbacks later
			var callback = this.getCallback('success');
			var errorCallback = this.getCallback('error');
			var alwaysCallback = this.getCallback('always');

			// Perform the AJAX call using jQuery's library
			jQuery.ajax({
				url: this.environment.url( uri ),
				data: this.configuration.parameters,
				dataType: 'json',
				type: this.configuration.requestMethod,
				success: function ( data ) {

					// If the programmer decides to follow our AJAX guidelines, they
					// can report either "success" and/or "error" keys in their JSON response
					var showError = data.error ? true : false;

					// We have to check this in two if-sentences, because if the data.success
					// key doesn't exist, the browsers will complain over the missing key on
					// comparison
					if ( typeof data.success === 'boolean' ) {
						if ( ! data.success ) {
							showError = true;
						}
					}

					// If it has been decided to show an error, we'll do so
					if ( showError ) {
						errorCallback( data.error );
					} else {
						callback( data );
					}

					// Perform the "always" callback
					alwaysCallback();

				},
				error: function ( jqXhr, dontCareAboutThis, errMsg ) {

					errorCallback( errMsg ? errMsg : jqXhr.responseText, jqXhr );
					alwaysCallback();

				}
			});

		}

	}