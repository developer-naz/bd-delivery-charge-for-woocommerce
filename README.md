<div align="center">

# 🚚 BD Delivery Charge for WooCommerce

**Inside Dhaka / Outside Dhaka delivery charges for Bangladeshi WooCommerce stores, done right.**

![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759B?style=flat&logo=wordpress&logoColor=white)
![WooCommerce](https://img.shields.io/badge/WooCommerce-8.0%2B-96588A?style=flat&logo=woocommerce&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat&logo=php&logoColor=white)
![Checkout block](https://img.shields.io/badge/Checkout%20block-supported-success?style=flat)
![Tests](https://img.shields.io/badge/end--to--end%20tests-passing-brightgreen?style=flat)
![License](https://img.shields.io/badge/license-GPL--2.0-orange?style=flat)

</div>

---

## ✨ What it does

Adds a **BD Delivery Charge** shipping method to WooCommerce. The charge is picked automatically from the customer's district (and thana):

~~~
Shipping:  Delivery charge (Inside Dhaka)    ৳60
           Delivery charge (Dhaka sub-area)  ৳90
           Delivery charge (Outside Dhaka)   ৳120
           Delivery charge (Chattogram)      ৳100   ← your own district rate
~~~

| Feature | Details |
|---|---|
| 🏙️ **Inside Dhaka** | The whole Dhaka district, **or** only Dhaka city thanas (Gulshan, Mirpur, Uttara, Dhanmondi…). Typed addresses like "Mirpur 10" or "Uttara Sector 7" are recognised. |
| 🏘️ **Dhaka sub-area** | Optional middle charge for Gazipur, Narayanganj (you choose the districts) and, in city mode, Savar, Keraniganj and the rest of Dhaka district. |
| 🌏 **Outside Dhaka** | Everything else. |
| 🗺️ **District rates** | Your own charge per district, by name or code: `Chattogram = 100`. |
| 🎁 **Free delivery** | Free above an amount, for all areas or Inside Dhaka only. |
| 🏷️ **Clear label** | Shows the area in the checkout, e.g. "Delivery charge (Inside Dhaka)". |

Works with the classic checkout, the **Checkout block**, Elementor checkout and the cart's shipping calculator.

> 💡 Pairs perfectly with [BD Upazila Dropdown](https://github.com/developer-naz/bd-upazila-dropdown-for-woocommerce): customers pick their thana from a list, so "Inside Dhaka city" is detected exactly.

---

## 🚀 Setup (2 minutes)

1. **Code → Download ZIP**, then **Plugins → Add New → Upload Plugin** and activate.
2. Go to **WooCommerce → Settings → Shipping → Add zone**.
3. Name it "Bangladesh", region **Bangladesh**, then **Add shipping method → BD Delivery Charge**.
4. Click **Edit** on the method and set your charges.

| Setting | Default |
|---|---|
| Inside Dhaka charge | ৳60 |
| "Inside Dhaka" means | The whole Dhaka district |
| Dhaka sub-area charge | Empty (uses Outside Dhaka) |
| Sub-area districts | Gazipur, Narayanganj |
| Outside Dhaka charge | ৳120 |
| District rates | Empty |
| Free delivery above | Off |

---

## 🧑‍💻 For developers

~~~php
// Add more areas that count as Dhaka city (used in "city" mode).
add_filter( 'bddc_dhaka_city_areas', function ( $areas ) {
	$areas[] = 'Bashundhara';
	return $areas;
} );

// Change the result: e.g. ৳20 extra for orders to Cox's Bazar (BD-11).
add_filter( 'bddc_area_and_cost', function ( $result, $state, $city ) {
	if ( 'BD-11' === $state ) {
		$result['cost'] += 20;
	}
	return $result;
}, 10, 3 );
~~~

The chosen area is saved on the order's shipping line as `bddc_area` (`inside`, `suburb`, `outside` or `district`).

---

## 🧪 Tested

Tested end-to-end on WordPress 6.8 + WooCommerce 10.3 (Twenty Twenty-Five theme, HPOS on):

- Dhaka → ৳60 Inside Dhaka, Chattogram → ৳120 Outside Dhaka
- Gazipur / Narayanganj → sub-area charge when set, otherwise outside
- "Mirpur" in **Kushtia** is *not* treated as Dhaka
- City mode: Savar → sub-area; "Mirpur 10" and "Uttara Sector 7" → Inside Dhaka
- District rates by name (`Chattogram = 100`) and code (`bd-60 = 130`); unknown names ignored
- Free delivery (inside only / all areas), non-Bangladesh addresses get no BD rate
- Checkout: changing the district updates the charge; the order is saved with the right delivery charge

---

## 📁 Files

~~~
bd-delivery-charge-for-woocommerce/
├── bd-delivery-charge-for-woocommerce.php     # Registers the shipping method
├── includes/class-bddc-shipping-method.php    # Settings and charge calculation
├── CHANGELOG.md
└── LICENSE
~~~

## 🗺️ Roadmap

- [ ] Weight-based extra charge (per kg)
- [ ] Upazila-level rates
- [ ] Courier-specific rates (Steadfast, Pathao, RedX)

## 📄 License

[GPL-2.0-or-later](LICENSE)

---

<div align="center">

### 👨‍💻 Built by Md Nazmul Islam (Developer Naz)

[![LinkedIn](https://img.shields.io/badge/LinkedIn-developernaz-0A66C2?style=flat&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/developernaz/)
[![GitHub](https://img.shields.io/badge/GitHub-developer--naz-181717?style=flat&logo=github&logoColor=white)](https://github.com/developer-naz)

More for Bangladeshi stores: [BD Upazila Dropdown](https://github.com/developer-naz/bd-upazila-dropdown-for-woocommerce) · [Fake Order Blocker](https://github.com/developer-naz/woocommerce-fake-order-blocker) · [Order on WhatsApp](https://github.com/developer-naz/order-on-whatsapp-for-woocommerce)

</div>
