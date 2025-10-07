<div class="col-sidebar">
	<ul>
		<li>
			<a href="javascript:void(0);" v-on:click.stop="data.fn.sidebar.toggle_item('general',data)"
				v-bind:class="{active:data.sidebar.items.general}">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd"
						d="M2.66667 8.66667H6.66667C7.03333 8.66667 7.33333 8.36667 7.33333 8V2.66667C7.33333 2.3 7.03333 2 6.66667 2H2.66667C2.3 2 2 2.3 2 2.66667V8C2 8.36667 2.3 8.66667 2.66667 8.66667ZM2.66667 14H6.66667C7.03333 14 7.33333 13.7 7.33333 13.3333V10.6667C7.33333 10.3 7.03333 10 6.66667 10H2.66667C2.3 10 2 10.3 2 10.6667V13.3333C2 13.7 2.3 14 2.66667 14ZM9.33333 14H13.3333C13.7 14 14 13.7 14 13.3333V8C14 7.63333 13.7 7.33333 13.3333 7.33333H9.33333C8.96667 7.33333 8.66667 7.63333 8.66667 8V13.3333C8.66667 13.7 8.96667 14 9.33333 14ZM8.66667 2.66667V5.33333C8.66667 5.7 8.96667 6 9.33333 6H13.3333C13.7 6 14 5.7 14 5.33333V2.66667C14 2.3 13.7 2 13.3333 2H9.33333C8.96667 2 8.66667 2.3 8.66667 2.66667Z"
						fill="#41278D" />
				</svg>
                <span><?php esc_attr_e( 'General', 'woocommerce-booking' ); // phpcs:ignore ?></span> <i
					class="fas fa-angle-right"></i>
			</a>
		</li>
		<li>
			<a href="javascript:void(0);"
				v-on:click.stop="data.fn.sidebar.toggle_item('availability',data)"
				v-bind:class="{active:data.sidebar.items.availability}">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd"
						d="M12.6667 2.66732H12V2.00065C12 1.63398 11.7 1.33398 11.3333 1.33398C10.9667 1.33398 10.6667 1.63398 10.6667 2.00065V2.66732H5.33333V2.00065C5.33333 1.63398 5.03333 1.33398 4.66667 1.33398C4.3 1.33398 4 1.63398 4 2.00065V2.66732H3.33333C2.59333 2.66732 2.00667 3.26732 2.00667 4.00065L2 13.334C2 14.0673 2.59333 14.6673 3.33333 14.6673H12.6667C13.4 14.6673 14 14.0673 14 13.334V4.00065C14 3.26732 13.4 2.66732 12.6667 2.66732ZM12.6667 12.6673C12.6667 13.034 12.3667 13.334 12 13.334H4C3.63333 13.334 3.33333 13.034 3.33333 12.6673V6.00065H12.6667V12.6673ZM4.66667 7.33398H6V8.66732H4.66667V7.33398ZM7.33333 7.33398H8.66667V8.66732H7.33333V7.33398ZM10 7.33398H11.3333V8.66732H10V7.33398Z"
						fill="#A7ACB1" />
				</svg>
                <span><?php esc_attr_e( 'Availability', 'woocommerce-booking' ); // phpcs:ignore ?></span> <i
					class="fas fa-angle-right"></i>
			</a>
		</li>
		<li v-show="'multiple_days' === data.general.settings.booking_type">
			<a href="javascript:void(0);"
				v-on:click.stop="data.fn.sidebar.toggle_item('block_pricing',data)"
				v-bind:class="{active:data.sidebar.items.block_pricing}">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd"
						d="M13.3359 2.66602H2.66927C1.92927 2.66602 1.3426 3.25935 1.3426 3.99935L1.33594 11.9993C1.33594 12.7393 1.92927 13.3327 2.66927 13.3327H13.3359C14.0759 13.3327 14.6693 12.7393 14.6693 11.9993V3.99935C14.6693 3.25935 14.0759 2.66602 13.3359 2.66602ZM12.6693 11.9993H3.33594C2.96927 11.9993 2.66927 11.6993 2.66927 11.3327V7.99935H13.3359V11.3327C13.3359 11.6993 13.0359 11.9993 12.6693 11.9993ZM13.3359 5.33268H2.66927V3.99935H13.3359V5.33268Z"
						fill="#A7ACB1" />
				</svg>
                <span><?php esc_attr_e( 'Block Pricing', 'woocommerce-booking' ); // phpcs:ignore ?></span> <i
					class="fas fa-angle-right"></i>
			</a>
		</li>
		<li>
			<a href="javascript:void(0);"
				v-on:click.stop="data.fn.sidebar.toggle_item('integrations',data)"
				v-bind:class="{active:data.sidebar.items.integrations}">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd"
						d="M12.435 5.56602L10.575 7.42602C10.3616 7.63935 10.5083 7.99935 10.8083 7.99935H12.0016C12.0016 10.206 10.2083 11.9993 8.00165 11.9993C7.47498 11.9993 6.96165 11.8993 6.50165 11.706C6.26165 11.606 5.98831 11.6793 5.80831 11.8593C5.46831 12.1993 5.58831 12.7727 6.03498 12.9527C6.64165 13.1993 7.30831 13.3327 8.00165 13.3327C10.9483 13.3327 13.335 10.946 13.335 7.99935H14.5283C14.8283 7.99935 14.975 7.63935 14.7616 7.43268L12.9016 5.57268C12.775 5.43935 12.5616 5.43935 12.435 5.56602ZM4.00165 7.99935C4.00165 5.79268 5.79498 3.99935 8.00165 3.99935C8.52832 3.99935 9.04165 4.09935 9.50165 4.29268C9.74165 4.39268 10.015 4.31935 10.195 4.13935C10.535 3.79935 10.415 3.22602 9.96832 3.04602C9.36165 2.79935 8.69498 2.66602 8.00165 2.66602C5.05498 2.66602 2.66831 5.05268 2.66831 7.99935H1.47498C1.17498 7.99935 1.02831 8.35935 1.24165 8.56602L3.10165 10.426C3.23498 10.5593 3.44165 10.5593 3.57498 10.426L5.43498 8.56602C5.64165 8.35935 5.49498 7.99935 5.19498 7.99935H4.00165Z"
						fill="#A7ACB1" />
				</svg>
                <span><?php esc_attr_e( 'Integrations', 'woocommerce-booking' ); // phpcs:ignore ?></span> <i
					class="fas fa-angle-right"></i>
			</a>
		</li>
		<li>
			<a href="javascript:void(0);" v-on:click.stop="data.fn.sidebar.toggle_item('resources',data)"
				v-bind:class="{active:data.sidebar.items.resources}">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd"
						d="M7.99594 1.33398C4.31594 1.33398 1.33594 4.32065 1.33594 8.00065C1.33594 11.6807 4.31594 14.6673 7.99594 14.6673C11.6826 14.6673 14.6693 11.6807 14.6693 8.00065C14.6693 4.32065 11.6826 1.33398 7.99594 1.33398ZM12.6159 5.33398H10.6493C10.4359 4.50065 10.1293 3.70065 9.72927 2.96065C10.9559 3.38065 11.9759 4.23398 12.6159 5.33398ZM8.0026 2.69398C8.55594 3.49398 8.98927 4.38065 9.27594 5.33398H6.72927C7.01594 4.38065 7.44927 3.49398 8.0026 2.69398ZM2.8426 9.33398C2.73594 8.90732 2.66927 8.46065 2.66927 8.00065C2.66927 7.54065 2.73594 7.09398 2.8426 6.66732H5.09594C5.0426 7.10732 5.0026 7.54732 5.0026 8.00065C5.0026 8.45398 5.0426 8.89398 5.09594 9.33398H2.8426ZM3.38927 10.6673H5.35594C5.56927 11.5007 5.87594 12.3007 6.27594 13.0407C5.04927 12.6207 4.02927 11.774 3.38927 10.6673ZM5.35594 5.33398H3.38927C4.02927 4.22732 5.04927 3.38065 6.27594 2.96065C5.87594 3.70065 5.56927 4.50065 5.35594 5.33398ZM8.0026 13.3073C7.44927 12.5073 7.01594 11.6207 6.72927 10.6673H9.27594C8.98927 11.6207 8.55594 12.5073 8.0026 13.3073ZM9.5626 9.33398H6.4426C6.3826 8.89398 6.33594 8.45398 6.33594 8.00065C6.33594 7.54732 6.3826 7.10065 6.4426 6.66732H9.5626C9.6226 7.10065 9.66927 7.54732 9.66927 8.00065C9.66927 8.45398 9.6226 8.89398 9.5626 9.33398ZM9.72927 13.0407C10.1293 12.3007 10.4359 11.5007 10.6493 10.6673H12.6159C11.9759 11.7673 10.9559 12.6207 9.72927 13.0407ZM10.9093 9.33398C10.9626 8.89398 11.0026 8.45398 11.0026 8.00065C11.0026 7.54732 10.9626 7.10732 10.9093 6.66732H13.1626C13.2693 7.09398 13.3359 7.54065 13.3359 8.00065C13.3359 8.46065 13.2693 8.90732 13.1626 9.33398H10.9093Z"
						fill="#A7ACB1" />
				</svg>
                <span><?php esc_attr_e( 'Resources', 'woocommerce-booking' ); // phpcs:ignore ?></span> <i
					class="fas fa-angle-right"></i>
			</a>
		</li>
		<li>
			<a href="javascript:void(0);" v-on:click.stop="data.fn.sidebar.toggle_item('persons',data)"
				v-bind:class="{active:data.sidebar.items.persons}">
				<svg width="14" height="10" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd"
						d="M10.0026 5.00065C10.9226 5.00065 11.6626 4.25398 11.6626 3.33398C11.6626 2.41398 10.9226 1.66732 10.0026 1.66732C9.0826 1.66732 8.33594 2.41398 8.33594 3.33398C8.33594 4.25398 9.0826 5.00065 10.0026 5.00065ZM5.0026 4.33398C6.10927 4.33398 6.99594 3.44065 6.99594 2.33398C6.99594 1.22732 6.10927 0.333984 5.0026 0.333984C3.89594 0.333984 3.0026 1.22732 3.0026 2.33398C3.0026 3.44065 3.89594 4.33398 5.0026 4.33398ZM10.0026 6.33398C8.7826 6.33398 6.33594 6.94732 6.33594 8.16732V9.00065C6.33594 9.36732 6.63594 9.66732 7.0026 9.66732H13.0026C13.3693 9.66732 13.6693 9.36732 13.6693 9.00065V8.16732C13.6693 6.94732 11.2226 6.33398 10.0026 6.33398ZM5.0026 5.66732C3.44927 5.66732 0.335938 6.44732 0.335938 8.00065V9.00065C0.335938 9.36732 0.635937 9.66732 1.0026 9.66732H5.0026V8.16732C5.0026 7.60065 5.2226 6.60732 6.5826 5.85398C6.0026 5.73398 5.4426 5.66732 5.0026 5.66732Z"
						fill="#A7ACB1" />
				</svg>
                <span><?php esc_attr_e( 'Persons', 'woocommerce-booking' ); // phpcs:ignore ?></span> <i
					class="fas fa-angle-right"></i>
			</a>
		</li>

		<li v-if="data.seasonal_pricing.settings.is_plugin_activated">
			<a href="javascript:void(0);"
				v-on:click.stop="data.fn.sidebar.toggle_item('seasonal_pricing',data)"
				v-bind:class="{active:data.sidebar.items.seasonal_pricing}">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd"
						d="M13.3359 2.66602H2.66927C1.92927 2.66602 1.3426 3.25935 1.3426 3.99935L1.33594 11.9993C1.33594 12.7393 1.92927 13.3327 2.66927 13.3327H13.3359C14.0759 13.3327 14.6693 12.7393 14.6693 11.9993V3.99935C14.6693 3.25935 14.0759 2.66602 13.3359 2.66602ZM12.6693 11.9993H3.33594C2.96927 11.9993 2.66927 11.6993 2.66927 11.3327V7.99935H13.3359V11.3327C13.3359 11.6993 13.0359 11.9993 12.6693 11.9993ZM13.3359 5.33268H2.66927V3.99935H13.3359V5.33268Z"
						fill="#A7ACB1" />
				</svg>
				<span><?php esc_attr_e( 'Seasonal Pricing', 'woocommerce-booking' ); // phpcs:ignore ?></span> <i
					class="fas fa-angle-right"></i>
			</a>
		</li>

		<li v-if="data.partial_payments.settings.is_plugin_activated">
			<a href="javascript:void(0);"
				v-on:click.stop="data.fn.sidebar.toggle_item('partial_payments',data)"
				v-bind:class="{active:data.sidebar.items.partial_payments}">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd"
						d="M7.33594 2.12078V13.8808C7.33594 14.3074 6.9426 14.6274 6.52927 14.5341C3.54927 13.8674 1.33594 11.1941 1.33594 8.00078C1.33594 4.80745 3.54927 2.13412 6.52927 1.46745C6.9426 1.37412 7.33594 1.69412 7.33594 2.12078ZM8.68927 2.12078V6.66078C8.68927 7.02745 8.98927 7.32745 9.35594 7.32745H13.8826C14.3093 7.32745 14.6293 6.93412 14.5359 6.51412C13.9693 4.00745 12.0026 2.03412 9.5026 1.46745C9.0826 1.37412 8.68927 1.69412 8.68927 2.12078ZM8.68927 9.34078V13.8808C8.68927 14.3074 9.0826 14.6274 9.5026 14.5341C12.0093 13.9675 13.9759 11.9874 14.5426 9.48078C14.6359 9.06745 14.3093 8.66745 13.8893 8.66745H9.3626C8.98927 8.67412 8.68927 8.97412 8.68927 9.34078Z"
						fill="#A7ACB1" />
				</svg>
                <span><?php esc_attr_e( 'Partial Payments', 'woocommerce-booking' ); // phpcs:ignore ?></span> <i
					class="fas fa-angle-right"></i>
			</a>
		</li>

		<li v-if="data.rental.settings.is_plugin_activated">
			<a href="javascript:void(0);" v-on:click.stop="data.fn.sidebar.toggle_item('rental',data)"
				v-bind:class="{active:data.sidebar.items.rental}">
				<svg width="14" height="12" viewBox="0 0 14 12" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd"
						d="M5.66576 10.6671V7.3338H8.33242V10.6671C8.33242 11.0338 8.63242 11.3338 8.99909 11.3338H10.9991C11.3658 11.3338 11.6658 11.0338 11.6658 10.6671V6.00047H12.7991C13.1058 6.00047 13.2524 5.62047 13.0191 5.42047L7.44576 0.400469C7.19242 0.173802 6.80576 0.173802 6.55242 0.400469L0.979089 5.42047C0.752422 5.62047 0.892422 6.00047 1.19909 6.00047H2.33242V10.6671C2.33242 11.0338 2.63242 11.3338 2.99909 11.3338H4.99909C5.36576 11.3338 5.66576 11.0338 5.66576 10.6671Z"
						fill="#A7ACB1" />
				</svg>
                <span><?php esc_attr_e( 'Rental Settings', 'woocommerce-booking' ); // phpcs:ignore ?></span> <i
					class="fas fa-angle-right"></i>
			</a>
		</li>

		<li>
			<a href="javascript:void(0);" id="bkap_booking_metabox_collapse_sidebar">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd"
						d="M12.0026 4.66732H4.0026C3.63594 4.66732 3.33594 4.36732 3.33594 4.00065C3.33594 3.63398 3.63594 3.33398 4.0026 3.33398H12.0026C12.3693 3.33398 12.6693 3.63398 12.6693 4.00065C12.6693 4.36732 12.3693 4.66732 12.0026 4.66732Z"
						fill="#A7ACB1" />
					<path fill-rule="evenodd" clip-rule="evenodd"
						d="M12.0026 8.66732H4.0026C3.63594 8.66732 3.33594 8.36732 3.33594 8.00065C3.33594 7.63398 3.63594 7.33398 4.0026 7.33398H12.0026C12.3693 7.33398 12.6693 7.63398 12.6693 8.00065C12.6693 8.36732 12.3693 8.66732 12.0026 8.66732Z"
						fill="#A7ACB1" />
					<path fill-rule="evenodd" clip-rule="evenodd"
						d="M12.0026 12.6673H4.0026C3.63594 12.6673 3.33594 12.3673 3.33594 12.0007C3.33594 11.634 3.63594 11.334 4.0026 11.334H12.0026C12.3693 11.334 12.6693 11.634 12.6693 12.0007C12.6693 12.3673 12.3693 12.6673 12.0026 12.6673Z"
						fill="#A7ACB1" />
				</svg>
                <span><?php esc_attr_e( 'Collapse Tabs', 'woocommerce-booking' ); // phpcs:ignore ?></span> <i
					class="fas fa-angle-right"></i>
			</a>
		</li>
	</ul>
</div>
