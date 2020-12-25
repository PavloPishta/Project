/*
http://sofwar.ru/
autor: SofWar
*/
package 
{
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.net.URLVariables;
	import flash.net.URLRequestMethod;
	import flash.display.*;
	import flash.events.*;
	import flash.text.TextField;
	import fl.containers.UILoader;
	import vii.api.serialization.json.JSON;
	import vii.api.MD5;

	public class Main extends Sprite
	{
		private var api_id:Number = 2;// ID приложения
		private var url_loader:URLLoader;
		private var url_request:URLRequest;
		private var viewer_id;
		private var secret:String;
		private var sid:String;
		private var api_url:String;

		private var bdate_tf:TextField = new TextField();
		private var name_tf:TextField = new TextField();
		private var last_tf:TextField = new TextField();
		private var rate_tf:TextField = new TextField();
		private var sex_tf:TextField = new TextField();
		private var avatar:UILoader = new UILoader;

		public function Main():void
		{
			// получаем данные из flashVars
			// id просматривающего приложение
			viewer_id = LoaderInfo(root.loaderInfo).parameters.viewer_id;
			// secret для генерации сигнатуры
			secret = LoaderInfo(root.loaderInfo).parameters.secret;
			// id сессии
			sid = LoaderInfo(root.loaderInfo).parameters.sid;
			// адрес API-сервиса
			api_url = LoaderInfo(root.loaderInfo).parameters.api_url;

			// для тестирования локально, можно прописать их здесь (брать в исходном коде страницы с приложением)
			
			/*viewer_id = '1';
			secret = 'abcd123456';
			sid = 'abcd123456abcd123456abcd123456abcd123456abcd123456abcd1234';
			api_url = 'http://buhacker.ru/api.php';
			*/
			// вызываем функцию получения профиля пользователя, просматривающего приложение
			getProfile(viewer_id);
		}

		private function getProfile(uid):void
		{
			// параметры которые будем передавать в запросе и которые нужны для формирования сигнатуры
			// параметры для формирования сигнатуры
			var request_params: Object = new Object();
			request_params.api_id = api_id;
			request_params.method = 'getProfiles';
			request_params.uids = uid;

			// параметры для отправки запроса
			var variables:URLVariables = new URLVariables();
			// часть параметров берем из request_params
			for (var j:String in request_params)
			{
				variables[j] = request_params[j];
			}
			variables['sid'] = sid;// параметр sid нужно передавать в запросе, но он не используются при создании сигнатуры
			variables['sig'] = generate_signature(request_params);// генерируем сигнатуру. Функция generate_signature описана ниже.

			// подготавливаем запрос
			url_request = new URLRequest(api_url);
			// данные будем отправлять POST запросом
			url_request.method = URLRequestMethod.POST;
			// добавляем параметры в запрос
			url_request.data = variables;
			// отправляем запрос
			url_loader = new URLLoader  ;
			url_loader.addEventListener(Event.COMPLETE,onComplete);
			url_loader.load(url_request);
		}
		
		// запрос выполнен
		private function onComplete(event:Event):void
		{
			var response:Object = vii.api.serialization.json.JSON.decode(url_loader.data);;

			// выводим дату рождения пользователя
			var bdate = 'Год рождения:' + response.bdate;
			bdate_tf.text = bdate;
			bdate_tf.width = 200;
			bdate_tf.x = 250;
			bdate_tf.y = 90;
			addChild(bdate_tf);

			// выводим имя пользователя
			name_tf.text = 'Имя: ' + response.first_name;
			name_tf.x = 250;
			name_tf.y = 30;
			addChild(name_tf);
			
			// выводим фамилию пользователя
			last_tf.text = 'Фамилия: ' + response.last_name;
			last_tf.x = 250;
			last_tf.y = 60;
			addChild(last_tf);
			
			// выводим пол пользователя
			var sex = '';
			if(response.sex == '1')sex = 'Мужской' else sex = 'Женский';
			if(response.sex == '0') sex = 'Не выбран';
			sex_tf.text = 'Пол: ' + sex;
			sex_tf.x = 250;
			sex_tf.y = 120;
			addChild(sex_tf);

			// выводим рейтинг пользователя
			rate_tf.text = 'Рейтинг: ' + response.rate + ' %';
			rate_tf.x = 250;
			rate_tf.y = 150;
			addChild(rate_tf);
			
			// выводим аватарку пользователя
			avatar.autoLoad = true;
			avatar.scaleContent = false;
			avatar.source = response.photo;
			avatar.move(10,30);
			addChild(avatar);
		}

		// функция генерации сигнатуры
		private function generate_signature(request_params):String
		{
			var signature = '';
			// сортируем параметры в алфавитном порядке
			var sorted_array: Array = new Array();
			for (var key in request_params)
			{
				sorted_array.push(key + "=" + request_params[key]);
			}
			sorted_array.sort();

			// создаем строку параметров;
			for (key in sorted_array)
			{
				signature +=  sorted_array[key];
			}
			signature = viewer_id + signature + secret;
			return vii.api.MD5.encrypt(signature);
		}
	}
}