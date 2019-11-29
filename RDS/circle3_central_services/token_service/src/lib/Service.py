from .Token import Token, Oauth2Token
from .User import User
from urllib.parse import urlparse, urlunparse
import requests, json
from requests.auth import HTTPBasicAuth
from datetime import datetime, timedelta

class Service():
    """
    Represents a service, which can be used in RDS.
    """

    _servicename = None

    def __init__(self, servicename: str):
        self.check_string(servicename, "servicename")

        self._servicename = servicename

    @property
    def servicename(self):
        return self._servicename

    def check_string(self, obj: str, string: str):
        if not obj:
            raise ValueError(f"{string} cannot be an empty string.")

    def is_valid(self, token: Token, user: User):
        pass

    def __eq__(self, obj):
        return (
            isinstance(obj, (Service)) and
            self.servicename == obj.servicename
        )

    def __str__(self):
        return f"Servicename: {self.servicename}"


class OAuth2Service(Service):
    """
    Represents an OAuth2 service, which can be used in RDS.
    """

    _refresh_url = None
    _authorize_url = None
    _client_id = None
    _client_secret = None

    def __init__(self, servicename: str, authorize_url: str, refresh_url: str, client_id: str, client_secret: str):
        super(OAuth2Service, self).__init__(servicename)

        self.check_string(authorize_url, "authorize_url")
        self.check_string(refresh_url, "refresh_url")
        self.check_string(client_id, "client_id")
        self.check_string(client_secret, "client_secret")

        self._authorize_url = self.parse_url(authorize_url)
        self._refresh_url = self.parse_url(refresh_url)

        self._client_id = client_id
        self._client_secret = client_secret

    def parse_url(self, url: str):
        u = urlparse(url)
        if not u.netloc:
            raise ValueError("URL needs a protocoll")

        # check for trailing slash for url
        if u.path and u.path[-1] == "/":
            u = u._replace(path=u.path[:-1])

        return u

    def refresh(self, token: Oauth2Token, user: User):
        """
        Refresh the given oauth2 token for specified user.
        """

        data = {
            "grant_type": "refresh_token"
        }

        req = requests.post(self.refresh_url, data=data, auth=HTTPBasicAuth(
            user.username, self.client_secret))

        data = json.loads(req.text)

        if not data["user_id"] == user.username:
            from .Exceptions.ServiceExceptions import TokenNotValidError
            raise TokenNotValidError(token, "User_ID in refresh response not equal to authenticated user.")

        date = datetime.now() + timedelta(seconds=data["expires_in"])
        return Oauth2Token(token.servicename, data["access_token"], data["refresh_token"], date)

    @property
    def refresh_url(self):
        return urlunparse(self._refresh_url)

    @property
    def authorize_url(self):
        return urlunparse(self._authorize_url)

    @property
    def client_id(self):
        return self._client_id

    @property
    def client_secret(self):
        return self._client_secret

    @classmethod
    def from_service(cls, service: Service, authorize_url: str, refresh_url: str, client_id: str, client_secret: str):
        return cls(service.servicename, authorize_url, refresh_url, client_id, client_secret)

    def __eq__(self, obj):
        return (
            super(OAuth2Service, self).__eq__(obj) and
            isinstance(obj, (OAuth2Service)) and
            self.refresh_url == obj.refresh_url and
            self.authorize_url == obj.authorize_url and
            self.client_id == obj.client_id and
            self.client_secret == obj.client_secret
        )

    def __str__(self):
        return f"{super(OAuth2Service, self).__str__()}, RefreshURL: {self.refresh_url}, AuthorizeURL: {self.authorize_url}"
