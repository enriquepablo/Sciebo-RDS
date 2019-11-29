
""" Module that monkey-patches json module when it's imported so
JSONEncoder.default() automatically checks for a special "to_json()"
method and uses it to encode the object if found.
"""
from json import JSONEncoder, JSONDecoder
from Util import storage

def to_default(self, obj):
    return getattr(obj.__class__, "__json__", to_default.default)(obj)


to_default.default = JSONEncoder.default  # Save unmodified default.
JSONEncoder.default = to_default  # Replace it.


