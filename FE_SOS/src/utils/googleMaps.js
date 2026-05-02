let googleMapsPromise = null;

const GOOGLE_MAPS_CALLBACK = "__sosGoogleMapsLoaded";
const GOOGLE_MAPS_SCRIPT_SELECTOR = "script[data-sos-google-maps]";

function buildGoogleMapsSrc(apiKey) {
  return `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&callback=${GOOGLE_MAPS_CALLBACK}&loading=async&v=weekly`;
}

function setGoogleMapsDebug(payload) {
  window.__sosGoogleMapsDebug = {
    ...(window.__sosGoogleMapsDebug || {}),
    ...payload,
    updatedAt: new Date().toISOString(),
  };
}

function cleanupGoogleMapsGlobals() {
  delete window[GOOGLE_MAPS_CALLBACK];
  delete window.gm_authFailure;
}

function removeGoogleMapsScript() {
  const script = document.querySelector(GOOGLE_MAPS_SCRIPT_SELECTOR);
  if (script) {
    script.remove();
  }
}

export function loadGoogleMaps() {
  if (window.google?.maps?.Map) {
    setGoogleMapsDebug({ stage: "already-loaded" });
    return Promise.resolve(window.google);
  }

  if (googleMapsPromise) {
    return googleMapsPromise;
  }

  const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY?.trim();

  if (!apiKey) {
    setGoogleMapsDebug({ stage: "missing-api-key" });
    return Promise.reject(new Error("VITE_GOOGLE_MAPS_API_KEY chua duoc cau hinh"));
  }

  const scriptSrc = buildGoogleMapsSrc(apiKey);
  const existingScript = document.querySelector(GOOGLE_MAPS_SCRIPT_SELECTOR);

  if (existingScript && existingScript.src !== scriptSrc) {
    removeGoogleMapsScript();
  }

  setGoogleMapsDebug({
    stage: "loading",
    scriptSrc,
    hasExistingScript: Boolean(existingScript),
  });

  googleMapsPromise = new Promise((resolve, reject) => {
    let settled = false;

    const finalize = (handler, value, shouldRemoveScript = false, stage = "unknown") => {
      if (settled) return;
      settled = true;
      clearInterval(checkLoaded);
      clearTimeout(timeoutId);
      cleanupGoogleMapsGlobals();
      setGoogleMapsDebug({
        stage,
        error: value instanceof Error ? value.message : null,
      });
      if (shouldRemoveScript) {
        removeGoogleMapsScript();
      }
      handler(value);
    };

    const resolveLoaded = () => finalize(resolve, window.google, false, "loaded");
    const rejectLoaded = (error, stage = "error") => finalize(reject, error, true, stage);

    const checkLoaded = setInterval(() => {
      if (window.google?.maps?.Map) {
        resolveLoaded();
      }
    }, 200);

    const timeoutId = setTimeout(() => {
      if (window.google?.maps?.Map) {
        resolveLoaded();
      } else {
        rejectLoaded(new Error("Google Maps timed out after 15 seconds"), "timeout");
      }
    }, 15000);

    window[GOOGLE_MAPS_CALLBACK] = () => {
      if (window.google?.maps?.Map) {
        resolveLoaded();
      } else {
        rejectLoaded(new Error("Google Maps callback fired before API was ready"), "callback-not-ready");
      }
    };

    window.gm_authFailure = () => {
      rejectLoaded(new Error("Google Maps API authentication failed"), "auth-failure");
    };

    let script = document.querySelector(GOOGLE_MAPS_SCRIPT_SELECTOR);

    if (script) {
      script.addEventListener("error", () => {
        rejectLoaded(new Error("Khong the tai Google Maps"), "script-error");
      }, { once: true });
      return;
    }

    script = document.createElement("script");
    script.dataset.sosGoogleMaps = "true";
    script.async = true;
    script.defer = true;
    script.src = scriptSrc;
    script.onerror = () => {
      rejectLoaded(new Error("Khong the tai Google Maps"), "script-error");
    };

    document.head.appendChild(script);
  }).catch((error) => {
    googleMapsPromise = null;
    throw error;
  });

  return googleMapsPromise;
}
