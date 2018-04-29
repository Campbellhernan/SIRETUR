{"filter":false,"title":"ForgotPasswordController.php","tooltip":"/app/Http/Controllers/Auth/ForgotPasswordController.php","undoManager":{"mark":80,"position":80,"stack":[[{"start":{"row":20,"column":5},"end":{"row":21,"column":0},"action":"insert","lines":["",""],"id":2},{"start":{"row":21,"column":0},"end":{"row":21,"column":4},"action":"insert","lines":["    "]}],[{"start":{"row":21,"column":4},"end":{"row":22,"column":0},"action":"insert","lines":["",""],"id":3},{"start":{"row":22,"column":0},"end":{"row":22,"column":4},"action":"insert","lines":["    "]}],[{"start":{"row":21,"column":0},"end":{"row":21,"column":4},"action":"remove","lines":["    "],"id":15}],[{"start":{"row":20,"column":5},"end":{"row":21,"column":0},"action":"remove","lines":["",""],"id":16}],[{"start":{"row":21,"column":0},"end":{"row":21,"column":4},"action":"remove","lines":["    "],"id":17}],[{"start":{"row":20,"column":5},"end":{"row":21,"column":0},"action":"remove","lines":["",""],"id":18}],[{"start":{"row":20,"column":5},"end":{"row":21,"column":0},"action":"insert","lines":["",""],"id":19},{"start":{"row":21,"column":0},"end":{"row":21,"column":4},"action":"insert","lines":["    "]}],[{"start":{"row":21,"column":4},"end":{"row":22,"column":0},"action":"insert","lines":["",""],"id":20},{"start":{"row":22,"column":0},"end":{"row":22,"column":4},"action":"insert","lines":["    "]}],[{"start":{"row":22,"column":4},"end":{"row":45,"column":5},"action":"insert","lines":[" public function sendResetLinkEmail(Request $request)","    {","        $this->validate($request, ['email' => 'required|email']);"," ","        $response = $this->broker()->sendResetLink(","            $request->only('email')","        );","  ","        switch ($response) {","            case \\Password::INVALID_USER:","                return response()->error($response, 422);","                break;"," ","            case \\Password::INVALID_PASSWORD:","                return response()->error($response, 422);","                break;"," ","            case \\Password::INVALID_TOKEN:","                return response()->error($response, 422);","                break;","            default: ","                return response()->success($response, 200);","        }","    }"],"id":21}],[{"start":{"row":26,"column":8},"end":{"row":44,"column":9},"action":"remove","lines":["$response = $this->broker()->sendResetLink(","            $request->only('email')","        );","  ","        switch ($response) {","            case \\Password::INVALID_USER:","                return response()->error($response, 422);","                break;"," ","            case \\Password::INVALID_PASSWORD:","                return response()->error($response, 422);","                break;"," ","            case \\Password::INVALID_TOKEN:","                return response()->error($response, 422);","                break;","            default: ","                return response()->success($response, 200);","        }"],"id":88},{"start":{"row":26,"column":8},"end":{"row":30,"column":11},"action":"insert","lines":["  $broker = $this->getBroker();","","        $response = Password::broker($broker)->sendResetLink($request->only('email'), function (Message $message) {","            $message->subject($this->getEmailSubject());","        });"]}],[{"start":{"row":26,"column":9},"end":{"row":26,"column":10},"action":"remove","lines":[" "],"id":89}],[{"start":{"row":26,"column":8},"end":{"row":26,"column":9},"action":"remove","lines":[" "],"id":90}],[{"start":{"row":30,"column":11},"end":{"row":31,"column":0},"action":"insert","lines":["",""],"id":91},{"start":{"row":31,"column":0},"end":{"row":31,"column":8},"action":"insert","lines":["        "]}],[{"start":{"row":31,"column":8},"end":{"row":32,"column":0},"action":"insert","lines":["",""],"id":92},{"start":{"row":32,"column":0},"end":{"row":32,"column":8},"action":"insert","lines":["        "]}],[{"start":{"row":32,"column":8},"end":{"row":32,"column":9},"action":"insert","lines":["r"],"id":93}],[{"start":{"row":32,"column":9},"end":{"row":32,"column":10},"action":"insert","lines":["e"],"id":94}],[{"start":{"row":32,"column":10},"end":{"row":32,"column":11},"action":"insert","lines":["t"],"id":95}],[{"start":{"row":32,"column":11},"end":{"row":32,"column":12},"action":"insert","lines":["u"],"id":96}],[{"start":{"row":32,"column":12},"end":{"row":32,"column":13},"action":"insert","lines":["r"],"id":97}],[{"start":{"row":32,"column":13},"end":{"row":32,"column":14},"action":"insert","lines":["n"],"id":98}],[{"start":{"row":32,"column":14},"end":{"row":32,"column":15},"action":"insert","lines":[" "],"id":99}],[{"start":{"row":32,"column":15},"end":{"row":32,"column":17},"action":"insert","lines":["\"\""],"id":100}],[{"start":{"row":32,"column":16},"end":{"row":32,"column":17},"action":"insert","lines":["H"],"id":101}],[{"start":{"row":32,"column":17},"end":{"row":32,"column":18},"action":"insert","lines":["o"],"id":102}],[{"start":{"row":32,"column":18},"end":{"row":32,"column":19},"action":"insert","lines":["l"],"id":103}],[{"start":{"row":32,"column":19},"end":{"row":32,"column":20},"action":"insert","lines":["a"],"id":104}],[{"start":{"row":32,"column":21},"end":{"row":32,"column":22},"action":"insert","lines":[";"],"id":105}],[{"start":{"row":26,"column":18},"end":{"row":26,"column":37},"action":"remove","lines":["$this->getBroker();"],"id":107},{"start":{"row":26,"column":18},"end":{"row":26,"column":37},"action":"insert","lines":["Password::broker();"]}],[{"start":{"row":28,"column":3},"end":{"row":30,"column":11},"action":"remove","lines":["     $response = Password::broker($broker)->sendResetLink($request->only('email'), function (Message $message) {","            $message->subject($this->getEmailSubject());","        });"],"id":108}],[{"start":{"row":26,"column":8},"end":{"row":26,"column":37},"action":"remove","lines":["$broker = Password::broker();"],"id":115}],[{"start":{"row":7,"column":0},"end":{"row":7,"column":40},"action":"insert","lines":["use Illuminate\\Support\\Facades\\Password;"],"id":116}],[{"start":{"row":7,"column":40},"end":{"row":8,"column":0},"action":"insert","lines":["",""],"id":117}],[{"start":{"row":25,"column":0},"end":{"row":30,"column":8},"action":"remove","lines":["        $this->validate($request, ['email' => 'required|email']);"," ","        ","","   ","        "],"id":118},{"start":{"row":25,"column":0},"end":{"row":34,"column":78},"action":"insert","lines":["        $this->validateEmail($request);","        // We will send the password reset link to this user. Once we have attempted","        // to send the link, we will examine the response then see the message we","        // need to show to the user. Finally, we'll send out a proper response.","        $response = $this->broker()->sendResetLink(","            $request->only('email')","        );","        return $response == Password::RESET_LINK_SENT","                    ? $this->sendResetLinkResponse($response)","                    : $this->sendResetLinkFailedResponse($request, $response);"]}],[{"start":{"row":32,"column":8},"end":{"row":32,"column":9},"action":"insert","lines":["/"],"id":119}],[{"start":{"row":32,"column":9},"end":{"row":32,"column":10},"action":"insert","lines":["*"],"id":120}],[{"start":{"row":34,"column":78},"end":{"row":34,"column":79},"action":"insert","lines":["*"],"id":121}],[{"start":{"row":34,"column":79},"end":{"row":34,"column":80},"action":"insert","lines":["/"],"id":122}],[{"start":{"row":29,"column":8},"end":{"row":29,"column":9},"action":"insert","lines":["/"],"id":123}],[{"start":{"row":29,"column":9},"end":{"row":29,"column":10},"action":"insert","lines":["*"],"id":124}],[{"start":{"row":31,"column":10},"end":{"row":31,"column":11},"action":"insert","lines":["*"],"id":125}],[{"start":{"row":31,"column":11},"end":{"row":31,"column":12},"action":"insert","lines":["/"],"id":126}],[{"start":{"row":29,"column":29},"end":{"row":29,"column":37},"action":"remove","lines":["broker()"],"id":127},{"start":{"row":29,"column":29},"end":{"row":29,"column":48},"action":"insert","lines":["Password::broker();"]}],[{"start":{"row":29,"column":47},"end":{"row":29,"column":48},"action":"remove","lines":[";"],"id":128}],[{"start":{"row":31,"column":11},"end":{"row":31,"column":12},"action":"remove","lines":["/"],"id":129}],[{"start":{"row":31,"column":10},"end":{"row":31,"column":11},"action":"remove","lines":["*"],"id":130}],[{"start":{"row":29,"column":9},"end":{"row":29,"column":10},"action":"remove","lines":["*"],"id":131}],[{"start":{"row":29,"column":8},"end":{"row":29,"column":9},"action":"remove","lines":["/"],"id":132}],[{"start":{"row":30,"column":12},"end":{"row":30,"column":13},"action":"insert","lines":["/"],"id":133}],[{"start":{"row":30,"column":13},"end":{"row":30,"column":14},"action":"insert","lines":["/"],"id":134}],[{"start":{"row":29,"column":61},"end":{"row":29,"column":63},"action":"insert","lines":["''"],"id":135}],[{"start":{"row":29,"column":62},"end":{"row":29,"column":63},"action":"remove","lines":["'"],"id":136}],[{"start":{"row":29,"column":61},"end":{"row":29,"column":62},"action":"remove","lines":["'"],"id":137}],[{"start":{"row":30,"column":13},"end":{"row":30,"column":14},"action":"remove","lines":["/"],"id":138}],[{"start":{"row":30,"column":12},"end":{"row":30,"column":13},"action":"remove","lines":["/"],"id":139}],[{"start":{"row":30,"column":12},"end":{"row":30,"column":35},"action":"remove","lines":["$request->only('email')"],"id":140}],[{"start":{"row":35,"column":15},"end":{"row":35,"column":21},"action":"remove","lines":["\"Hola\""],"id":141},{"start":{"row":35,"column":15},"end":{"row":35,"column":38},"action":"insert","lines":["$request->only('email')"]}],[{"start":{"row":30,"column":12},"end":{"row":30,"column":35},"action":"insert","lines":["$request->only('email')"],"id":142}],[{"start":{"row":35,"column":15},"end":{"row":35,"column":39},"action":"remove","lines":["$request->only('email');"],"id":147},{"start":{"row":35,"column":15},"end":{"row":35,"column":33},"action":"insert","lines":["Password::broker()"]}],[{"start":{"row":29,"column":7},"end":{"row":29,"column":8},"action":"insert","lines":["/"],"id":148}],[{"start":{"row":29,"column":8},"end":{"row":29,"column":9},"action":"insert","lines":["*"],"id":149}],[{"start":{"row":31,"column":10},"end":{"row":31,"column":11},"action":"insert","lines":["*"],"id":150}],[{"start":{"row":31,"column":11},"end":{"row":31,"column":12},"action":"insert","lines":["/"],"id":151}],[{"start":{"row":35,"column":33},"end":{"row":35,"column":34},"action":"insert","lines":[";"],"id":152}],[{"start":{"row":7,"column":40},"end":{"row":8,"column":0},"action":"insert","lines":["",""],"id":153}],[{"start":{"row":8,"column":0},"end":{"row":8,"column":40},"action":"insert","lines":["Illuminate\\Contracts\\Auth\\PasswordBroker"],"id":154}],[{"start":{"row":8,"column":0},"end":{"row":8,"column":1},"action":"insert","lines":["u"],"id":155}],[{"start":{"row":8,"column":1},"end":{"row":8,"column":2},"action":"insert","lines":["s"],"id":156}],[{"start":{"row":8,"column":2},"end":{"row":8,"column":3},"action":"insert","lines":["e"],"id":157}],[{"start":{"row":8,"column":3},"end":{"row":8,"column":4},"action":"insert","lines":[" "],"id":158}],[{"start":{"row":8,"column":44},"end":{"row":8,"column":45},"action":"insert","lines":[";"],"id":159}],[{"start":{"row":6,"column":0},"end":{"row":6,"column":1},"action":"insert","lines":["/"],"id":162}],[{"start":{"row":6,"column":1},"end":{"row":6,"column":2},"action":"insert","lines":["/"],"id":163}],[{"start":{"row":8,"column":0},"end":{"row":8,"column":45},"action":"remove","lines":["use Illuminate\\Contracts\\Auth\\PasswordBroker;"],"id":164},{"start":{"row":8,"column":0},"end":{"row":9,"column":0},"action":"insert","lines":["",""]}],[{"start":{"row":8,"column":0},"end":{"row":9,"column":0},"action":"remove","lines":["",""],"id":165}],[{"start":{"row":7,"column":40},"end":{"row":8,"column":0},"action":"insert","lines":["",""],"id":176}],[{"start":{"row":8,"column":0},"end":{"row":8,"column":45},"action":"insert","lines":["use Illuminate\\Contracts\\Auth\\PasswordBroker;"],"id":177}],[{"start":{"row":6,"column":1},"end":{"row":6,"column":2},"action":"remove","lines":["/"],"id":178}],[{"start":{"row":6,"column":0},"end":{"row":6,"column":1},"action":"remove","lines":["/"],"id":179}],[{"start":{"row":8,"column":0},"end":{"row":8,"column":45},"action":"remove","lines":["use Illuminate\\Contracts\\Auth\\PasswordBroker;"],"id":186}],[{"start":{"row":7,"column":40},"end":{"row":8,"column":0},"action":"remove","lines":["",""],"id":187}],[{"start":{"row":7,"column":40},"end":{"row":8,"column":0},"action":"remove","lines":["",""],"id":188}]]},"ace":{"folds":[],"scrolltop":60,"scrollleft":0,"selection":{"start":{"row":6,"column":31},"end":{"row":6,"column":55},"isBackwards":true},"options":{"guessTabSize":true,"useWrapMode":false,"wrapToView":true},"firstLineState":{"row":4,"state":"php-start","mode":"ace/mode/php"}},"timestamp":1521430264578,"hash":"e832effcd546ebadcb08a88a50c80305bfbd394f"}