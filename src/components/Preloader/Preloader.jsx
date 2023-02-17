import { useState } from "react";
import "./Preloader.css";

const Preloader = () => {
    const [showLoader, setShowLoader] = useState(true);

    setTimeout(() => {
        setShowLoader(false);
    }, 1000);

    return showLoader ? (
        <div id="loader" className="show">
            <div className="loader"></div>
        </div>
    ) : null;
};

export default Preloader;